<?php

return [
    'source' => [
        'appointo' => 1,
        'mindbody' => 2,
    ],
    'reverse_source' => [
        1 => 'appointo',
        2 => 'mindbody',
    ],
    'sync' =>[
        'yes' => 1,
        'no' => 0,
    ],
    'api_version' => env('API_VERSION', '2025-04'),
    'mindbody_api_url' => 'https://api.mindbodyonline.com/public/v6/',
    'staff_id' => env('STAFF_ID', 100000258),
    'location_id' => env('LOCATION_ID', 1),
    'session_type_id' => env('SESSION_TYPE_ID', 200),
    'default_birthdate' => env('DEFAULT_BIRTHDATE', '1/1/2001'),
];
