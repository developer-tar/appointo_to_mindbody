<?php

namespace App\Console\Commands;

use App\Jobs\SyncMindBodyToAppointo;
use Illuminate\Console\Command;

class MindBodyToAppointo extends Command
{
    protected $signature = 'app:mindbody-to-appointo';
    protected $description = 'Fetch booked appointments from Mindbody and store them in DB for Appointo';

    public function handle()
    {
        $this->info("Fetching Mindbody booked appointments...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());

        $limit = 200;
        $offset = 0;
        $totalResults = null;

        do {
            $this->line("Fetching appointments: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/appointment/staffappointments', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error('Failed to fetch Mindbody Staff appointments: ' . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['Appointments']) || !is_array($data['Appointments'])) {
                $this->error('Invalid response format: "Appointments" key missing or not an array.');
                return Command::FAILURE;
            }

            $appointments = $data['Appointments'];
            
            $chunks = array_chunk($appointments, 50);
            dd($chunks);
            foreach ($chunks as $chunk) {
                SyncMindBodyToAppointo::dispatch($chunk);
            }

            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($appointments);
            $offset += $limit;

        } while ($offset < $totalResults);

        $this->info('✅ All Mindbody appointments fetched and dispatched successfully.');
        return Command::SUCCESS;
    }
}
