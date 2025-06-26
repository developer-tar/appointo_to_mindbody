<?php

namespace App\Console\Commands;

use App\Jobs\UpdateMindBodyLocation;
use Illuminate\Console\Command;

class GetMindBodyLocation extends Command
{
    protected $signature = 'app:get-mind-body-location';
    protected $description = 'Fetch and dispatch MindBody Locations using pagination';

    public function handle()
    {
        $this->info("Fetching MindBody Locations...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());

        $limit = 200;
        $offset = 0;
        $totalResults = null;

        do {
            $this->line("Fetching locations: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/site/locations', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error("Failed to fetch locations at offset {$offset}: " . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['Locations']) || !is_array($data['Locations'])) {
                $this->error("Invalid response format at offset {$offset}.");
                return Command::FAILURE;
            }

            $locations = $data['Locations'];

            $chunks = array_chunk($locations, 5);
            foreach ($chunks as $chunk) {
                UpdateMindBodyLocation::dispatch($chunk);
            }

            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($locations);
            $offset += $limit;

        } while ($offset < $totalResults);

        $this->info('✅ All MindBody Locations fetched and dispatched successfully.');
        return Command::SUCCESS;
    }
}
