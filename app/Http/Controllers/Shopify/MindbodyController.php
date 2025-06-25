<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\MindbodyAppointment;
use App\Models\MindbodyClient;
use App\Models\MindbodyToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class MindbodyController extends Controller {

    private function http($token = null) {
        return Http::acceptJson()
            ->withHeaders([
                'API-Key' => config('services.mindbody.key'),
                'SiteId'  => config('services.mindbody.site_id'),
            ])
            ->when($token, fn($req) => $req->withToken($token, 'Bearer'));
    }

    /**
     * Get or refresh Mindbody staff access token.
     */
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
            'expires_at'   => now()->addSeconds($resp['ExpiresIn']),
        ]);

        return $resp['AccessToken'];
    }

    /**
     * Find or create a client in Mindbody.
     */
    private function ensureClient(array $clientData, string $token): string {
        $resp = $this->http($token)
            ->post(config('services.mindbody.base') . '/client/addclient', $clientData)
            ->throw()
            ->json();

        return $resp['ClientId'];
    }

    /**
     * Get all available appointment types from Mindbody.
     */
    public function appointmentTypes() {
        $token = $this->accessToken();

        return $this->http($token)
            ->get(config('services.mindbody.base') . '/appointment/appointmenttypes')
            ->throw()
            ->json();
    }

    /**
     * Book a Mindbody appointment and store the local record.
     */
    public function book(Request $request) {
        $shop = auth()->user(); // Osiset handles Shopify store context

        $data = $request->validate([
            'first_name'          => 'required|string',
            'last_name'           => 'required|string',
            'email'               => 'required|email',
            'mobile'              => 'required|string',
            'location_id'         => 'required|integer',
            'staff_id'            => 'required|integer',
            'starts_at'           => 'required|date',
            'ends_at'             => 'required|date|after:starts_at',
            'appointment_type_id' => 'required|integer',
            'session_type_id'     => 'nullable|integer',
        ]);

        $token = $this->accessToken();

        // STEP 2: Add/find client on Mindbody
        $clientId = $this->ensureClient([
            'FirstName'   => $data['first_name'],
            'LastName'    => $data['last_name'],
            'Email'       => $data['email'],
            'MobilePhone' => $data['mobile'],
        ], $token);

        // STEP 3: Book appointment
        $resp = $this->http($token)
            ->post(config('services.mindbody.base') . '/appointment/addappointment', [
                'ClientId'         => $clientId,
                'LocationId'       => $data['location_id'],
                'StaffId'          => $data['staff_id'],
                'StartDateTime'    => Carbon::parse($data['starts_at'])->toIso8601String(),
                'EndDateTime'      => Carbon::parse($data['ends_at'])->toIso8601String(),
                'AppointmentTypeId' => $data['appointment_type_id'],
                'SessionTypeId'    => $data['session_type_id'],
            ])
            ->throw()
            ->json();

        // Store client locally
        $client = MindbodyClient::updateOrCreate(
            ['mindbody_client_id' => $clientId],
            [
                'shop_id'     => $shop->id,
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'email'       => $data['email'],
                'phone'       => $data['mobile'],
            ]
        );

        // Store appointment locally
        $local = MindbodyAppointment::create([
            'shop_id'                => $shop->id,
            'mindbody_client_id'     => $client->id,
            'mindbody_appointment_id' => $resp['Id'] ?? null,
            'appointment_type_id'    => $data['appointment_type_id'],
            'session_type_id'        => $data['session_type_id'],
            'location_id'            => $data['location_id'],
            'staff_id'               => $data['staff_id'],
            'starts_at'              => $data['starts_at'],
            'ends_at'                => $data['ends_at'],
        ]);

        return response()->json([
            'message' => 'Appointment booked successfully',
            'local'   => $local,
            'mindbody_response' => $resp,
        ]);
    }
    public function bookFromAppointo(array $payload) {
        $shop = $payload['shop'];
        $data = $payload['appointo_data'];

        $token = $this->accessToken();

        $start = Carbon::parse($data['timestring']);
        $end = (clone $start)->addMinutes(30);

        $clientId = $this->ensureClient([
            'FirstName' => explode(' ', $data['name'])[0],
            'LastName' => explode(' ', $data['name'])[1] ?? '',
            'Email' => $data['email'],
            'MobilePhone' => $data['phone'] ?? '0000000000',
        ], $token);

        $resp = $this->http($token)->post(config('services.mindbody.base') . '/appointment/addappointment', [
            'ClientId' => $clientId,
            'LocationId' => 1,
            'StaffId' => 1,
            'StartDateTime' => $start->toIso8601String(),
            'EndDateTime' => $end->toIso8601String(),
            'AppointmentTypeId' => 1,
            'SessionTypeId' => null,
        ])->throw()->json();

        $client = MindbodyClient::updateOrCreate(
            ['mindbody_client_id' => $clientId],
            [
                'shop_id' => $shop->id,
                'first_name' => explode(' ', $data['name'])[0],
                'last_name' => explode(' ', $data['name'])[1] ?? '',
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]
        );

        return MindbodyAppointment::create([
            'shop_id' => $shop->id,
            'mindbody_client_id' => $client->id,
            'mindbody_appointment_id' => $resp['Id'] ?? null,
            'appointment_type_id' => 1,
            'session_type_id' => null,
            'location_id' => 1,
            'staff_id' => 1,
            'starts_at' => $start,
            'ends_at' => $end,
        ]);
    }
}
