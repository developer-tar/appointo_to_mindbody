<?php

use Illuminate\Support\Facades\Http;
use App\Models\MindbodyToken;

if (!function_exists('getMindbodyAccessToken')) {
    function getMindbodyAccessToken(): string
    {
        $stored = MindbodyToken::latest()->first();

        if ($stored && $stored->expires_at->isFuture()) {
            return $stored->access_token;
        }

        $resp = mindbodyHttpClient()->post(
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

if (!function_exists('mindbodyHttpClient')) {
    function mindbodyHttpClient(?string $token = null)
    {
        $client = Http::acceptJson()->withHeaders([
            'API-Key' => config('services.mindbody.key'),
            'SiteId'  => config('services.mindbody.site_id'),
        ]);

        return $token ? $client->withToken($token, 'Bearer') : $client;
    }
}
if (!function_exists('appointoHttpClient')) {
    function appointoHttpClient()
    {
        return Http::withHeaders([
            'APPOINTO-TOKEN' => config('services.appointo.key'),
        ]);
    }
}
