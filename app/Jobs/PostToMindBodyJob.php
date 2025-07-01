<?php 
namespace App\Jobs;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostToMindBodyJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointments;

    public function __construct($appointments) {
        $this->appointments = $appointments;
           
    }

    public function handle() {
        $mindBodyHttp = mindbodyHttpClient(getMindbodyAccessToken());
       
        foreach ($this->appointments as $mindBody) {
           
            $response = $mindBodyHttp->post(config('services.mindbody.base') . '/appointment/addappointment', [
                'ClientId' => $mindBody['client_id'],
                'LocationId' => $mindBody['location_id'] ?? 1,
                'StaffId' => $mindBody['staff_id'],
                'StartDateTime' => $mindBody['timestring'],
                'SessionTypeId' => $mindBody['session_type_id'],
                'ApplyPayment' => false,
                'StaffRequested' => true,
                'Test' => false,
            ]);

            if ($response->failed()) {
                Log::error(['❌ Failed to book in MindBody' => $mindBody['id'], 'body' => $response->body()]);
                continue;
            }

            $appointmentData = $response->json('Appointment');
            if (!is_array($appointmentData)) {
                Log::error(['❌ Invalid Appointment data in MindBody response' => $mindBody['id']]);
                continue;
            }

            BookingAppointment::where('id', $mindBody['id'])->update([
                'after_sync_json_data' => $appointmentData,
                'is_sync' => config('constants.sync.yes'),
            ]);
        }
    }
}
