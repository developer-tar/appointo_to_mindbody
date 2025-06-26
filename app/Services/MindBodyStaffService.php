<?php 
namespace App\Services;

use App\Models\MindbodyToken;
use Illuminate\Support\Facades\Http;

class MindBodyStaffService {
    protected $baseUrl;

    public function __construct() {
        
        $this->baseUrl = config('services.mindbody.base'); 
    }

    public function getStaffAppointments($limit = 100, $offset = 0) {
        $url = "{$this->baseUrl}/appointment/staffappointments?limit={$limit}&offset={$offset}";
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'API-Key'      => config('services.mindbody.key'),
            'SiteId'       => config('services.mindbody.site_id'),
            'Authorization'=> 'Bearer ' . $this->getAccessToken(),
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch staff appointments: ' . $response->body());
        }
        dd($response->json());
        return $response->json();
    }

    private function getAccessToken(): string {
        $stored = MindbodyToken::latest()->first();

        if ($stored && $stored->expires_at->isFuture()) {
            return $stored->access_token;
        }

        $resp = Http::post(
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
}
