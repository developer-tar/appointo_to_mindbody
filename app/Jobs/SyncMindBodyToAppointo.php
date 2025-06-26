<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;

class SyncMindBodyToAppointo implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
     protected array $appointments;

    public function __construct(array $appointments)
    {
        $this->appointments = $appointments;
    }

    public function handle(): void
    {
        foreach ($this->appointments as $appointment) {
            AppointoAppointment::updateOrCreate(
                ['appointment_id' => $appointment['id']],
                [
                    'activate'                 => $appointment['activate'],
                    'product_uuid'             => $appointment['product_uuid'],
                    'duration_uuid'            => $appointment['duration_uuid'],
                    'product_detail_id'        => $appointment['product_detail_id'],
                    'name'                     => $appointment['name'],
                    'price'                    => $appointment['price'],
                    'currency'                 => $appointment['currency'],
                    'appointment_config'       => $appointment['appointment_config'],
                    'team_members'             => $appointment['team_members'],
                    'groups'                   => $appointment['groups'],
                    'weekly_availabilities'    => $appointment['weekly_availabilities'],
                    'overridden_availabilities'=> $appointment['overridden_availabilities'],
                    'custom_fields'            => $appointment['custom_fields'],
                ]
            );
        }
    }
}
