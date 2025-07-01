<?php

namespace App\Console\Commands;

use App\Models\BookingAppointment;
use Illuminate\Console\Command;
use App\Jobs\PostToAppointoJob;
use App\Jobs\PostToMindBodyJob;

class PostBookAppointment extends Command
{
    protected $signature = 'app:post-book-appointment';
    protected $description = 'Sync unbooked appointments from BookingAppointment to Appointo and Mindbody';

    public function handle()
    {
        // Appointo source (from MindBody side)
        $appointoAppointments = BookingAppointment::unsynced()
            ->where('source', config('constants.source.mindbody'))
            ->select('id', 'email', 'timestring', 'name', 'appointment_id')
            ->get();

        // MindBody source (from Appointo side)
        $mindbodyAppointments = BookingAppointment::unsynced()
            ->where('source', config('constants.source.appointo'))
            ->select('id', 'client_id', 'location_id', 'session_type_id', 'staff_id', 'timestring')
            ->get();

        // ✅ Dispatch Appointo Jobs
        if ($appointoAppointments->isNotEmpty()) {
            $appointoAppointments->chunk(5)->each(function ($chunk) {
                PostToAppointoJob::dispatch($chunk);
            });
        }

        // ✅ Dispatch MindBody Jobs
        if ($mindbodyAppointments->isNotEmpty()) {
            $mindbodyAppointments->chunk(5)->each(function ($chunk) {
                PostToMindBodyJob::dispatch($chunk);
            });
        }

        $this->info('✅ Appointment sync jobs dispatched successfully.');
    }
}
