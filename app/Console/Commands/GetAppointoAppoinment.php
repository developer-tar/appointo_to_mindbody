<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateAppointoAppointment;

class GetAppointoAppoinment extends Command
{
    protected $signature = 'app:get-appointo-appointment';
    protected $description = 'Fetch Appointo appointments and store them into DB';

    public function handle()
    {
        $this->info("Fetching Appointo appointments...");

        $response = appointoHttpClient()->get(config('services.appointo.base') . '/appointments');
    
        if ($response->failed()) {
            $this->error('Failed to fetch Appointo appointments: ' . $response->body());
            return Command::FAILURE;
        }   

        $appointments = $response->json();
 
        if (!is_array($appointments)) {
            $this->error('Invalid response format: expected JSON array.');
            return Command::FAILURE;
        }
        
        $chunks = array_chunk($appointments, 50);
       
        foreach ($chunks as $chunk) {
            UpdateAppointoAppointment::dispatch($chunk);
        }

        $this->info('Fetched Appointo appointments...');
        return Command::SUCCESS;
    }
}
