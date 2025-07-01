<?php

namespace App\Console\Commands;

use App\Jobs\SyncMindBodyToAppointoJob;

use Illuminate\Console\Command;

class MindBodyToAppointo extends Command {
    protected $signature = 'app:mindbody-to-appointo';
    protected $description = 'Fetch booked appointments from Mindbody and sync to Appointo & Shopify';
   
    public function handle() {
        $this->line("Fetching Mindbody appointments...");
        SyncMindBodyToAppointoJob::dispatch();
        $this->line("Syncing Mindbody appointments to Appointo...");
    }
}
