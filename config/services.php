<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'appointo' => [
        'key'  => env('APPOINTO_API_KEY'),
        'base' => 'https://app.appointo.me/api',
    ],

    'mindbody' => [
        'key'      => env('MINDBODY_API_KEY'),
        'site_id'  => env('MINDBODY_SITE_ID'),
        'username' => env('MINDBODY_STAFF_USER'),
        'password' => env('MINDBODY_STAFF_PASS'),
        'base'     => 'https://api.mindbodyonline.com/public/v6',
    ],

];
