<?php

namespace App\Console\Commands;

use App\Jobs\UpdateMindBodyClient;
use Illuminate\Console\Command;

class GetMindBodyClient extends Command {
    protected $signature = 'app:get-mind-body-client';
    protected $description = 'Fetch and queue all MindBody Clients using pagination';

    public function handle() {
        $this->info("Fetching all MindBody Clients with pagination...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());

        $limit = 200; // Max usually accepted
        $offset = 0;
        $totalResults = null;

        do {
            $this->line("Fetching clients: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/client/clients', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error("Failed to fetch clients at offset {$offset}: " . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['Clients']) || !is_array($data['Clients'])) {
                $this->error("Invalid response format at offset {$offset}.");
                return Command::FAILURE;
            }

            $clients = $data['Clients'];

            // Dispatch in smaller chunks if needed
            $chunks = array_chunk($clients, 5);
            foreach ($chunks as $chunk) {
                UpdateMindBodyClient::dispatch($chunk);
            }

            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($clients);
            $offset += $limit;

        } while ($offset < $totalResults);

        $this->info('✅ All MindBody Clients fetched and dispatched successfully.');
        return Command::SUCCESS;
    }
}
