<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    // IMPORTANT: No trailing slashes, exact matches only
    'allowed_origins' => [
        'http://localhost:3005',
        'http://localhost',
        env('FRONTEND_URL', 'http://localhost:3005'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Usually you don't need these unless returning custom headers
    'exposed_headers' => [],

    'max_age' => 3600,

    // If you use axios with withCredentials: true or fetch(credentials: 'include')
    'supports_credentials' => true,
];