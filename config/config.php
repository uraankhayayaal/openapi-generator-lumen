<?php

return [
    'title' => 'API DOCUMENTATION',
    'version' => '0.0.1',
    'servers' => [
        'local' => [
            'host' => 'http://localhost:8000',
            'description' => 'Local API server',
        ],
        'prod' => [
            'host' => 'https://example.domain',
            'description' => 'Production API server',
        ],
    ],
    'auth_middleware' => 'auth',
    'export' => [
        'path' => './openapi.json',
        'format' => 'json',
    ],
];
