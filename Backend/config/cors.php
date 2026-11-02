<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_filter([
        env('FRONTEND_URL', 'http://localhost:5173'),
        'https://futureself.in',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,

];
