<?php

namespace App\Jobs;

use App\Models\MindBodyClient;
use App\Models\MindBodySession;
use App\Models\AppointoAppointment;
use App\Models\BookingAppointment;
use App\Models\ShopifyCustomer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncMindBodyToAppointoJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle() {
        Log::info("🟢 SyncMindBodyAppointmentsJob started");

        $user = User::first();
        $accessToken = $user->password;
        $baseUrl = $user->name;
        $apiVersion = config('constants.api_version');

        $http = mindbodyHttpClient(getMindbodyAccessToken());
        $limit = 200;
        $offset = 0;

        do {
            $response = $http->get(config('services.mindbody.base') . '/appointment/staffappointments', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                Log::error('❌ Failed to fetch Mindbody appointments: ' . $response->body());
                return;
            }

            $data = $response->json();
            $appointments = $data['Appointments'] ?? [];
            $appointments = $data['Appointments'] ?? [];
            $appointments = [
                [
                    "GenderPreference" => "None",
                    "Duration" => 60,
                    "ProviderId" => "0",
                    "Id" => 100046496,
                    "Status" => "NoShow",
                    "StartDateTime" => "2025-06-25T11:00:00",
                    "EndDateTime" => "2025-06-25T12:00:00",
                    "Notes" => null,
                    "StaffRequested" => true,
                    "ProgramId" => 1,
                    "SessionTypeId" => 200,
                    "LocationId" => 1,
                    "StaffId" => 100000108,
                    "Staff" => null,
                    "ClientId" => "100015639",
                    "FirstAppointment" => true,
                    "IsWaitlist" => false,
                    "WaitlistEntryId" => null,
                    "ClientServiceId" => null,
                    "Resources" => null,
                    "AddOns" => null,
                    "OnlineDescription" => "A 60 minute session that will blast your muscles!"
                ]
            ];

            if (!is_array($appointments)) continue;

            foreach ($appointments as $appointment) {
                $sessionName = MindBodySession::where('mindbody_session_id', $appointment['SessionTypeId'])->value('name');
                if (!$sessionName) continue;

                $appointoAppointmentId = AppointoAppointment::where('name', 'like', "%{$sessionName}%")->value('appointment_id');
                if (!$appointoAppointmentId) continue;

                $client = MindBodyClient::where('mindbody_client_id', $appointment['ClientId'])->first();
                if (!$client || empty($client->email)) continue;

                $email = trim($client->email);
                $shopifyCustomer = ShopifyCustomer::where('email', $email)->first();

                if (!$shopifyCustomer) {
                    try {
                        $headers = ['X-Shopify-Access-Token' => $accessToken];
                        $url = "https://{$baseUrl}/admin/api/{$apiVersion}/customers.json";

                        $searchResponse = Http::withHeaders($headers)->get($url, ['query' => "email:$email"]);
                        $searchResults = $searchResponse->json('customers') ?? [];

                        if (count($searchResults)) {
                            $shopifyCustomer = $this->storeShopifyCustomer($searchResults[0]);
                        } else {
                            $createResponse = Http::withHeaders($headers)->post($url, [
                                'customer' => [
                                    'email' => $email,
                                    'first_name' => $client->first_name,
                                    'last_name' => $client->last_name ?? '',
                                    'phone' => $client->phone ?? null,
                                ]
                            ]);

                            if ($createResponse->successful()) {
                                $shopifyCustomer = $this->storeShopifyCustomer($createResponse->json('customer'));
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("⚠️ Shopify sync failed for {$email}: " . $e->getMessage());
                    }
                }

                try {
                    $name = trim($client->first_name . ' ' . $client->last_name);
                    $searchTerm = $client->email ?: $name;

                    $appointoResponse = appointoHttpClient()->get(config('services.appointo.base') . '/bookings', [
                        'search_term' => $searchTerm,
                    ]);

                    $appointoResults = $appointoResponse->json('data') ?? [];

                    $alreadyBooked = collect($appointoResults)->contains(function ($booking) use ($appointment, $client, $name, $appointoAppointmentId) {
                        if (!isset($booking['customers'][0])) return false;
                        $customer = $booking['customers'][0];

                        return Carbon::parse($booking['selected_time'])->toDateTimeString() === Carbon::parse($appointment['StartDateTime'])->toDateTimeString()
                            && strtolower(trim($customer['email'])) === strtolower(trim($client->email))
                            && strtolower(trim($customer['name'])) === strtolower(trim($name))
                            && $customer['appointment_id'] === $appointoAppointmentId;
                    });

                    if ($alreadyBooked) continue;

                    BookingAppointment::updateOrCreate(
                        [
                            'appointment_id' => $appointoAppointmentId,
                            'source' => config('constants.source.mindbody'),
                            'email' => $client->email,
                        ],
                        [
                            'timestring' => Carbon::parse($appointment['StartDateTime'])->setTimezone('UTC')->format('Y-m-d\TH:i:s.000\Z'),
                            'name' => $name,
                            'phone' => $client->phone ?? null,
                            'is_sync' => config('constants.sync.no'),
                            'source_json_data' => json_encode($appointment),
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("⚠️ Booking save failed for {$client->email}: " . $e->getMessage());
                }
            }

            $offset += $limit;
            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($appointments);
        } while ($offset < $totalResults);

        Log::info('✅ Mindbody appointments synced successfully');
    }

    private function storeShopifyCustomer(array $shopifyData): ShopifyCustomer {
        return ShopifyCustomer::updateOrCreate(
            ['customer_id' => $shopifyData['id']],
            [
                'email' => $shopifyData['email'],
                'first_name' => $shopifyData['first_name'],
                'last_name' => $shopifyData['last_name'],
                'phone' => $shopifyData['phone'] ?? null,
                'verified_email' => $shopifyData['verified_email'],
                'state' => $shopifyData['state'],
                'tags' => $shopifyData['tags'],
                'currency' => $shopifyData['currency'],
                'default_address' => $shopifyData['default_address'] ?? null,
                'shopify_created_at' => $shopifyData['created_at'],
                'shopify_updated_at' => $shopifyData['updated_at'],
                'json_data' => $shopifyData,
            ]
        );
    }
}
