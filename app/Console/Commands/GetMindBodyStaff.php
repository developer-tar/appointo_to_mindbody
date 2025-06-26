<?php

namespace App\Console\Commands;

use App\Jobs\UpdateMindBodyStaff;
use Illuminate\Console\Command;

class GetMindBodyStaff extends Command
{
    protected $signature = 'app:get-mind-body-staff';
    protected $description = 'Fetch and dispatch MindBody Staff using pagination';

    public function handle()
    {
        $this->info("Fetching MindBody Staff...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());

        $limit = 200;
        $offset = 0;
        $totalResults = null;

        do {
            $this->line("Fetching staff: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/Staff/Staff', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error("Failed to fetch staff at offset {$offset}: " . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['StaffMembers']) || !is_array($data['StaffMembers'])) {
                $this->error("Invalid response format at offset {$offset}.");
                return Command::FAILURE;
            }

            $staffs = $data['StaffMembers'];

            $chunks = array_chunk($staffs, 5);
            foreach ($chunks as $chunk) {
                UpdateMindBodyStaff::dispatch($chunk);
            }

            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($staffs);
            $offset += $limit;

        } while ($offset < $totalResults);

        $this->info('✅ All MindBody Staff fetched and dispatched successfully.');

        return Command::SUCCESS;
    }
}
