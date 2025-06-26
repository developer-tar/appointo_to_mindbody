<?php

namespace App\Jobs;

use App\Models\MindBodySession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateMindBodySession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $sessions;

    public function __construct(array $sessions)
    {
        $this->sessions = $sessions;
    }

    public function handle(): void
    {
        foreach ($this->sessions as $session) {
            try {
                MindBodySession::updateOrCreate(
                    ['mindbody_session_id' => $session['Id']],
                    [
                        'type' => $session['Type'] ?? null,
                        'default_time_length' => $session['DefaultTimeLength'] ?? null,
                        'staff_time_length' => $session['StaffTimeLength'] ?? null,
                        'name' => $session['Name'] ?? null,
                        'online_description' => $session['OnlineDescription'] ?? null,
                        'num_deducted' => $session['NumDeducted'] ?? null,
                        'program_id' => $session['ProgramId'] ?? null,
                        'category' => $session['Category'] ?? null,
                        'category_id' => $session['CategoryId'] ?? null,
                        'subcategory' => $session['Subcategory'] ?? null,
                        'subcategory_id' => $session['SubcategoryId'] ?? null,
                        'available_for_add_on' => $session['AvailableForAddOn'] ?? false,
                        'json_data' => $session,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Failed to update MindBody session', [
                    'session_id' => $session['Id'] ?? 'unknown',
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
