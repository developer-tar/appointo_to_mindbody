<?php

namespace App\Console\Commands;

use App\Models\BookingAppointment;
use App\Models\MindBodyClient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AppointoToMindBody extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:appointo-to-mind-body';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
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
    /**
     * Execute the console command.
     */
    public function handle() {
        try {
            $response  = appointoHttpClient()->get(config('services.appointo.base') . '/bookings');

            if (!$response->successful()) {
                return response()->json([
                    'error' => $response->json(),
                    'message' => 'Getting failed the Appointo API'
                ], $response->status());
            }

            // ✅ Save Appointo booking locally
            $appointoData = $response->json();

            $http = mindbodyHttpClient(getMindbodyAccessToken());
            $token = $this->accessToken();

            foreach ($appointoData as $data) {

                $customer = $data['customers'][0];
                $name = $customer['name'];

                $firstName = explode(' ', $name)[0];
                $LastName =  explode(' ', $name)[1];

                $email = $customer['email'];

                $clientId = MindBodyClient::where([
                    'email' => $email,
                ])->value('id');

                if (!$clientId) {
                    $response = $http->post(config('services.mindbody.base') . '/client/addclient', [
                        "Email" => $email,
                        "FirstName" => $firstName,
                        "LastName" => $LastName,
                        "Birthdate" => "1/1/2001",
                    ]);
                    if($response->failed()){
                        continue;
                    }
                    if($response->successfull()){
                         $data = $response->json();
                         $client = $data['Clients'];
                         $clientId = $client['id'];
                    }
                }
              
                $staffId = 100000258;
                
                $resp = $http($token)->post(config('services.mindbody.base') . '/appointment/addappointment', [
                    'ClientId' => $clientId,
                    'LocationId' => 1,
                    'StaffId' => 100000258,
                    'StartDateTime' => Carbon::parse($data['selected_time'])->format('Y-m-d\TH:i'),
                    'SessionTypeId' => 200,
                    'ApplyPayment' => false,
                    'StaffRequested' => true,
                    "Test" => true,
                ])->throw()->json();

                // Store locally
                BookingAppointment::updateOrCreate(
                    [
                        'source' => config('constants.source.appointo'),
                        'email' => $client->email,
                    ],
                    [
                        'appointment_id' => $resp['Appointment']['Id'] ?? null,
                        'json_data' => json_encode($resp),
                        'appointment_type_id' => 1,
                        'session_type_id' => null,
                        'location_id' => 1,
                        'staff_id' => $staffId,
                        'starts_at' => $data['selected_time'],
                        'is_sync' => config('constants.sync.no'),
                        'source' => config('constants.source.mindbody'),
                        'json_data' => $appointment,
                    ]
                );
            }

            return response()->json([
                'message'   => 'Booking created and synced successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store: ' . $e->getMessage());
        }
    }
}
