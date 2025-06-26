<?php

namespace App\Console\Commands;

use App\Jobs\UpdateMindBodySession;
use Illuminate\Console\Command;

class GetMindBodySession extends Command
{
    protected $signature = 'app:get-mind-body-session';
    protected $description = 'Fetch and dispatch MindBody Session Types using pagination';

    public function handle()
    {
        $this->info("Fetching MindBody Session Types...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());

        $limit = 200;
        $offset = 0;
        $totalResults = null;

        do {
            $this->line("Fetching sessions: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/site/sessiontypes', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error("Failed to fetch sessions at offset {$offset}: " . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['SessionTypes']) || !is_array($data['SessionTypes'])) {
                $this->error("Invalid response format at offset {$offset}.");
                return Command::FAILURE;
            }

            $sessions = $data['SessionTypes'];

            $chunks = array_chunk($sessions, 5);
            foreach ($chunks as $chunk) {
                UpdateMindBodySession::dispatch($chunk);
            }

            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($sessions);
            $offset += $limit;

        } while ($offset < $totalResults);

        $this->info('✅ All MindBody Session Types fetched and dispatched successfully.');
        return Command::SUCCESS;
    }
}
