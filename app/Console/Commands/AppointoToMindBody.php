<?php 
namespace App\Console\Commands;

use App\Jobs\SyncAppointoToMindBodyJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AppointoToMindBody extends Command
{
    protected $signature = 'app:appointo-to-mind-body';
    protected $description = 'Fetch Appointo bookings and dispatch jobs to sync to MindBody';

    public function handle()
    {
        $appointoResponse = appointoHttpClient()->get(config('services.appointo.base') . '/bookings');

        if (!$appointoResponse->successful()) {
            Log::error('❌ Failed to fetch Appointo bookings', [
                'response' => $appointoResponse->json(),
                'status' => $appointoResponse->status(),
            ]);
            return Command::FAILURE;
        }

        $appointoData = $appointoResponse->json();

        foreach ($appointoData as $booking) {
            
            SyncAppointoToMindBodyJob::dispatch($booking);
        }
        $this->info('✅ All Appointo bookings dispatched to job queue.');
        return Command::SUCCESS;
    }
}
