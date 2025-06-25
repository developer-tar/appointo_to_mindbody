<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\AppointoBooking;
use App\Models\MindbodyAppointment;
use App\Models\MindbodyClient;
use App\Models\MindbodyToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller {
    /**
     * Make Appointo HTTP client
     */
    private function appointo() {
        return Http::withHeaders([
            'APPOINTO-TOKEN' => config('services.appointo.token'),
        ]);
    }

    private function http($token = null) {
        return Http::acceptJson()
            ->withHeaders([
                'API-Key' => config('services.mindbody.key'),
                'SiteId'  => config('services.mindbody.site_id'),
            ])
            ->when($token, fn($req) => $req->withToken($token, 'Bearer'));
    }

    private function accessToken(): string {
        $stored = MindbodyToken::latest()->first();

        if ($stored && $stored->expires_at->isFuture()) {
            return $stored->access_token;
        }

        $resp = $this->http()->post(
            config('services.mindbody.base') . '/usertoken/issue',
            [
                'Username' => config('services.mindbody.username'),
                'Password' => config('services.mindbody.password'),
            ]
        )->throw()->json();

        MindbodyToken::create([
            'access_token' => $resp['AccessToken'],
            'expires_at'   => $resp['Expires'],
        ]);

        return $resp['AccessToken'];
    }
    private function ensureClient(array $clientData, string $token): string {
    
        $resp = $this->http($token)
            ->post(config('services.mindbody.base') . '/client/addclient', $clientData)
            ->throw()
            ->json();
        
        return $resp['Client']['Id'];
    }
    /**
     * Create a new Appointo booking
     */
    public function getRecord(Request $request) {
        try {

            $response = $this->appointo()
                ->get(config('services.appointo.base') . '/bookings');

            if (!$response->successful()) {
                return response()->json([
                    'error' => $response->json(),
                    'message' => 'Getting failed at Appointo API'
                ], $response->status());
            }
            $shop = User::all();
            $shopId = $shop[0]->id;
            // ✅ Save Appointo booking locally
            $appointoData = $response->json();
            
            $token = $this->accessToken();

            foreach ($appointoData as $data) {
          
                $customer = $data['customers'][0];
                // $name = $customer['name'];
                
                // $firstName = explode(' ', $name)[0];
                // $LastName =  explode(' ', $name)[1];
                
                // $email = $customer['email'];
                // $birthdate = '1/1/1997';

                // $existingClient = MindbodyClient::where([
                //     'first_name' => $firstName,
                //     'last_name' => $LastName,
                //     'email' => $email,
                //     'birthdate' => $birthdate
                // ])->first();

                // if (!$existingClient) {
             
                //     // 2. Only call ensureClient if no match
                //     $clientId = $this->ensureClient([
                //         'FirstName' => $firstName,
                //         'LastName' => $LastName ?? '',
                //         'Email' => $email,
                //         'WorkPhone' => $customer['payload']['phone'] ?? '0000000000',
                //         'State' => 'CA',
                //         'Birthdate' => $birthdate,
                //         'Test' => 'false'
                //     ], $token);
                   
                //     // 3. Store or update local client record
                //     $client = MindbodyClient::updateOrCreate(
                //         ['mindbody_client_id' => $clientId],
                //         [
                //             'shop_id' => $shopId,
                //             'first_name' => $firstName,
                //             'last_name' => $lastName ?? '',
                //             'email' => $email,
                //             'birthdate' => $birthdate,
                //             'phone' => $data['phone'] ?? null,
                //         ]
                //     );
                // } else {
                //     $client = $existingClient;
                //     $clientId = $client->mindbody_client_id;
                // }
                // $clientId =100013338;
                $clientId =100015634;
                $data['selected_time'] = "2025-06-09T10:00";
                // $staffId =100000052;
                $staffId =100000258;

                $resp = $this->http($token)->post(config('services.mindbody.base') . '/appointment/addappointment', [
                    'ClientId' => $clientId,
                    'LocationId' => 1,
                    'StaffId' => $staffId,
                    'StartDateTime' => $data['selected_time'],
                    'SessionTypeId' => 200,
                    'ApplyPayment' => false,
                    'StaffRequested'=>true,
                    "Test" => true,
                ])->throw()->json();
                
                MindbodyAppointment::create([
                    'shop_id' => $shopId,
                    'mindbody_appointment_id' => $resp['Appointment']['Id'] ?? null,
                    'json_data' => json_encode($resp),
                    'appointment_type_id' => 1,
                    'session_type_id' => null,
                    'location_id' => 1,
                    'staff_id' => $staffId,
                    'starts_at' => $data['selected_time'],
                ]);
            }

            return response()->json([
                'message'   => 'Booking created and synced successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule an Appointo booking
     */
    public function reschedule(Request $request) {
        $payload = $request->validate([
            'booking_id' => 'required|string',
            'timestring' => 'required|string',
        ]);

        return $this->appointo()
            ->post(config('services.appointo.base') . '/bookings/reschedule', $payload)
            ->throw()
            ->json();
    }

    /**
     * Cancel an Appointo booking
     */
    public function cancel(Request $request) {
        $payload = $request->validate([
            'booking_id'   => 'required|string',
            'customer_ids' => 'required|array',
        ]);

        return $this->appointo()
            ->post(config('services.appointo.base') . '/bookings/cancel', $payload)
            ->throw()
            ->json();
    }

    /**
     * List all Appointo bookings for this store
     */
    public function index() {
        $shop = auth()->user();

        $bookings = AppointoBooking::where('shop_id', $shop->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }
}
