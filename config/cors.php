<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000', 'http://192.168.1.5:3000', 'https://www.aurevejewels.com', 'https://app.aurevejewels.com'], 'https://aurevejewels.com',
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];