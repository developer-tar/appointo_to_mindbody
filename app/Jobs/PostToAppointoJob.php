<?php

namespace App\Jobs;

use App\Models\BookingAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class PostToAppointoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointments;

    public function __construct($appointments)
    {
        $this->appointments = $appointments;
    }

    public function handle()
    {
        $appointoHttp = appointoHttpClient();

        foreach ($this->appointments as $appointo) {
            $response = $appointoHttp->post(config('services.appointo.base') . '/bookings', [
                'appointment_id' => $appointo->appointment_id,
                'timestring' => $appointo->timestring,
                'email' => $appointo->email,
                'name' => $appointo->name,
            ]);

            if ($response->failed()) {
                Log::error(['❌ Failed to book in Appointo' => $appointo->id, 'body' => $response->body()]);
                continue;
            }

            BookingAppointment::where('id', $appointo->id)->update([
                'after_sync_json_data' => $response->json(),
                'is_sync' => config('constants.sync.yes')
            ]);
        }
    }
}