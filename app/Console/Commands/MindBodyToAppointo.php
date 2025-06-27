<?php

namespace App\Console\Commands;

use App\Models\MindBodyClient;
use App\Models\MindBodySession;
use App\Models\AppointoAppointment;
use App\Models\BookingAppointment;
use App\Models\ShopifyCustomer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MindBodyToAppointo extends Command {
    protected $signature = 'app:mindbody-to-appointo';
    protected $description = 'Fetch booked appointments from Mindbody and sync to Appointo & Shopify';
    protected $baseUrl;
    protected $accessToken;
    protected $apiVersion;

    public function __construct() {
        parent::__construct();

        $user = User::first();
        $this->accessToken = $user->password;
        $this->baseUrl = $user->name;
        $this->apiVersion = config('constants.api_version');
    }

    public function handle() {
        $this->info("Fetching Mindbody booked appointments...");

        $http = mindbodyHttpClient(getMindbodyAccessToken());
        $limit = 200;
        $offset = 0;

        do {
            $this->line("Fetching appointments: offset=$offset, limit=$limit");

            $response = $http->get(config('services.mindbody.base') . '/appointment/staffappointments', [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                $this->error('❌ Failed to fetch Mindbody appointments: ' . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();
            $appointments = $data['Appointments'] ?? [];

            if (!is_array($appointments)) {
                $this->error('❌ Invalid response: Appointments not found.');
                return Command::FAILURE;
            }

            foreach ($appointments as $appointment) {
               
                // Get session name
                $sessionName = MindBodySession::where('mindbody_session_id', $appointment['SessionTypeId'])->value('name');
                if (!$sessionName) continue;

                // Get Appointo appointment ID using LIKE
                $appointoAppointmentId = AppointoAppointment::where('name', 'like', "%{$sessionName}%")->value('appointo_appointment_id');
                if (!$appointoAppointmentId) {
                    $this->warn("⚠️ No Appointo appointment matched for session name like: %{$sessionName}%");
                    continue;
                }

                // Get Mindbody client
                $client = MindBodyClient::where('mindbody_client_id', $appointment['ClientId'])->first();
                if (!$client || empty($client->email)) continue;

                $email = trim($client->email);
                $shopifyCustomer = ShopifyCustomer::where('email', $email)->first();

                if (!$shopifyCustomer) {
                    try {
                        $headers = [
                            'X-Shopify-Access-Token' => $this->accessToken,
                        ];

                        $searchResponse = Http::withHeaders($headers)
                            ->get($this->getShopifyCustomerUrl(), [
                                'query' => "email:$email"
                            ]);

                        $searchResults = $searchResponse->json('customers') ?? [];

                        if (count($searchResults)) {
                            $shopifyData = $searchResults[0];
                            $shopifyCustomer = $this->storeShopifyCustomer($shopifyData);
                        } else {
                            $createResponse = Http::withHeaders($headers)
                                ->post($this->getShopifyCustomerUrl(), [
                                    'customer' => [
                                        'email' => $email,
                                        'first_name' => $client->first_name,
                                        'last_name' => $client->last_name ?? '',
                                        'phone' => $client->phone ?? null,
                                    ]
                                ]);

                            if ($createResponse->successful()) {
                                $shopifyData = $createResponse->json('customer');
                                $shopifyCustomer = $this->storeShopifyCustomer($shopifyData);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("⚠️ Shopify customer sync failed for {$email}: " . $e->getMessage());
                    }
                }

                try {
                    $name = $client->first_name . ' ' . $client->last_name;
                    $searchTerm = $client->email ?: ($name);

                    $appointoResponse = appointoHttpClient()->get(config('services.appointo.base') . '/bookings', [
                        'search_term' => $searchTerm,
                    ]);

                    $appointoResults = $appointoResponse->json('data') ?? $appointoResponse->json() ?? [];

                    $alreadyBooked = collect($appointoResults)->contains(function ($booking) use ($appointment, $client, $name, $appointoAppointmentId) {
                        if (!isset($booking['customers'][0])) return false;

                        $customer = $booking['customers'][0];

                        $appointoTime = isset($booking['selected_time']) ? Carbon::parse($booking['selected_time'])->toDateTimeString() : null;
                        $mindbodyTime = isset($appointment['StartDateTime']) ? Carbon::parse($appointment['StartDateTime'])->toDateTimeString() : null;

                        return
                            $customer &&
                            $appointoTime === $mindbodyTime &&
                            strtolower(trim($customer['email'])) === strtolower(trim($client->email)) &&
                            strtolower(trim($customer['name'])) === strtolower(trim($name)) &&
                            $customer['appointment_id'] === $appointoAppointmentId;
                    });

                    if ($alreadyBooked) {
                        $this->line("⏩ Already booked: {$client->email} at {$appointment['StartDateTime']}");
                        continue;
                    }

                    // Store locally
                    BookingAppointment::updateOrCreate(
                        [
                            'appointment_id' => $appointoAppointmentId,
                            'source' => config('constants.source.mindbody'),
                            'email' => $client->email,
                        ],
                        [
                            'appointment_id' => $appointoAppointmentId,
                            'timestring' => $appointment['StartDateTime'],
                            'email' => $client->email,
                            'name' => trim("{$client->first_name} {$client->last_name}"),
                            'phone' => $client->phone ?? null,
                            'is_sync' => config('constants.sync.no'),
                            'source' => config('constants.source.mindbody'),
                            'json_data' => $appointment,
                        ]
                    );
                } catch (\Exception $e) {
                    $this->error("⚠️ Appointo booking check failed: " . $e->getMessage());
                }
            }

            $offset += $limit;
            $totalResults = $data['PaginationResponse']['TotalResults'] ?? count($appointments);
        } while ($offset < $totalResults);

        $this->info('✅ All Mindbody appointments processed and synced.');
        return Command::SUCCESS;
    }

    private function getShopifyCustomerUrl(): string {
        return "https://{$this->baseUrl}/admin/api/{$this->apiVersion}/customers.json";
    }

    private function storeShopifyCustomer(array $shopifyData): ShopifyCustomer {
        return ShopifyCustomer::create([
            'customer_id' => $shopifyData['id'],
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
        ]);
    }
}
