<?php

namespace App\Jobs;

use App\Models\BookingAppointment;
use App\Models\MindBodyClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAppointoToMindBodyJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function handle() {
        try {
            $customer = $this->data['customers'][0] ?? null;
            if (!$customer || !isset($customer['email'], $customer['name'])) {
                Log::warning('⚠️ Missing customer email or name in Appointo data.');
                return;
            }

            $email = $customer['email'];
            $nameParts = explode(' ', $customer['name']);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $http = mindbodyHttpClient(getMindbodyAccessToken());

            $clientId = MindBodyClient::where('email', $email)->value('mindbody_client_id');

            if (!$clientId) {
                $clientResponse = $http->post(config('services.mindbody.base') . '/client/addclient', [
                    "Email" => $email,
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                    "Birthdate" => config('constants.default_birthdate'),
                ]);

                if ($clientResponse->failed()) {
                    Log::warning("⚠️ Failed to create MindBody client for $email");
                    return;
                }

                $clientData = $clientResponse->json('Clients');
                $clientId = $clientData['id'] ?? null;
                if (!$clientId) {
                    Log::warning("⚠️ MindBody client creation returned no ID for $email");
                    return;
                }
            }

            $staffId = config('constants.staff_id');
            $locationId = config('constants.location_id');
            $sessionTypeId = config('constants.session_type_id');
            $selectedTime = $this->data['selected_time'] ?? null;

            if (!$staffId || !$clientId || !$selectedTime || !$locationId || !$sessionTypeId) {
                Log::warning("⚠️ Missing booking info for $email");
                return;
            }

            $formattedTime = Carbon::parse($selectedTime)->format('Y-m-d\TH:i');

            BookingAppointment::updateOrCreate(
                [
                    'source' => config('constants.source.appointo'),
                    'email' => $email,
                    'timestring' => $formattedTime,
                ],
                [
                    'source_json_data' =>json_encode($this->data),
                    'session_type_id' => $sessionTypeId,
                    'location_id' => $locationId,
                    'staff_id' => $staffId,
                    'client_id' => $clientId,
                    'is_sync' => config('constants.sync.no'),
                ]
            );

            Log::info("✅ Synced booking for $email at $formattedTime");

        } catch (\Exception $e) {
            Log::error("❌ Sync failed: " . $e->getMessage());
        }
    }
}
