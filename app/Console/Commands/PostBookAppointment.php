<?php

namespace App\Console\Commands;

use App\Models\BookingAppointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PostBookAppointment extends Command {
    protected $signature = 'app:post-book-appointment';
    protected $description = 'Sync unbooked appointments from BookingAppointment to Appointo and Mindbody';

    public function handle() {
        $unsyncedAppointments = BookingAppointment::where('is_sync', config('constants.sync.no'));

        $appointoAppointments = $unsyncedAppointments
            ->where('source', config('constants.source.mindbody'))
            ->select('id', 'email', 'timestring', 'name', 'appointment_id')
            ->get();

        $mindbodyAppointments = $unsyncedAppointments
            ->where('source', config('constants.source.appointo'))
            ->select('id', 'client_id', 'location_id', 'session_type_id', 'staff_id', 'timestring')
            ->get();

        // ✅ Sync to Appointo
        if ($appointoAppointments->isNotEmpty()) {
            $http = mindbodyHttpClient(getMindbodyAccessToken());

            foreach ($appointoAppointments as $appointo) {
                $response = appointoHttpClient()->post(config('services.appointo.base') . '/bookings', [
                    'appointment_id' => $appointo->appointment_id,
                    'timestring' => Carbon::parse($appointo->timestring)->toIso8601String(),
                    'email' => $appointo->email,
                    'name' => $appointo->name,
                ]);

                if ($response->failed()) {
                    Log::error(['❌ Failed to book in Appointo' => $appointo->id]);
                    continue;
                }

                BookingAppointment::where('id', $appointo->id)->update([
                    'after_sync_json_data' => $response->json(),
                    'is_sync' => config('constants.sync.yes')
                ]);
            }
        }

        // ✅ Sync to MindBody
        if ($mindbodyAppointments->isNotEmpty()) {
            $http = mindbodyHttpClient(getMindbodyAccessToken());

            foreach ($mindbodyAppointments as $mindBody) {
                $response = $http->post(config('services.mindbody.base') . '/appointment/addappointment', [
                    'ClientId' => $mindBody->client_id,
                    'LocationId' => $mindBody->location_id ?? 1,
                    'StaffId' => $mindBody->staff_id,
                    'StartDateTime' => Carbon::parse($mindBody->timestring)->format('Y-m-d\TH:i'),
                    'SessionTypeId' => $mindBody->session_type_id,
                    'ApplyPayment' => false,
                    'StaffRequested' => true,
                    'Test' => false,
                ]);

                if ($response->failed()) {
                    Log::error(['❌ Failed to book in MindBody' => $mindBody->id]);
                    continue;
                }

                $appointmentData = $response->json('Appointment');
                if (!is_array($appointmentData)) {
                    Log::error(['❌ Invalid Appointment data in MindBody response' => $mindBody->id]);
                    continue;
                }

                BookingAppointment::where('id', $mindBody->id)->update([
                    'after_sync_json_data' => $appointmentData,
                    'is_sync' => config('constants.sync.yes'),
                ]);
            }
        }

        $this->info('✅ Appointment posting completed.');
    }
}
