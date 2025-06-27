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
];
