<?php

return [

    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie', 'reviews/*'],

    'allowed_methods' => ['*'],

    // Asegurate de usar solo los orígenes que realmente usás
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
