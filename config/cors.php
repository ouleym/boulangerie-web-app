<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:4200',
        'http://127.0.0.1:4200',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        // Ajoutez d'autres domaines si nécessaire
    ],

    'allowed_origins_patterns' => [
        // Vous pouvez utiliser des patterns si nécessaire
        // '/localhost:\d+/',
    ],

    'allowed_headers' => [
        'Accept',
        'Accept-Encoding',
        'Accept-Language',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Cache-Control',
        'Pragma',
        'X-Auth-Token',
        'Origin',
        'Connection',
        'User-Agent',
        'Referer',
        'Access-Control-Allow-Origin',
        'Access-Control-Allow-Headers',
        'Access-Control-Allow-Methods',
        'Access-Control-Allow-Credentials',
    ],

    'exposed_headers' => [
        'X-CSRF-TOKEN',
        'Set-Cookie',
        'Authorization',
    ],

    'max_age' => 86400, // 24 heures

    'supports_credentials' => true,
];
