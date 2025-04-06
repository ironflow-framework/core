<?php

return 
[
    'max_payload_size' => env('CHANNEL_MAX_PAYLOAD_SIZE', 10240),
    
    // Authentification
    'auth' => [
        'enabled' => env('CHANNEL_AUTH_ENABLED', true),
        'middleware' => env('CHANNEL_AUTH_MIDDLEWARE', 'auth'),
        'route_prefix' => env('CHANNEL_AUTH_ROUTE_PREFIX', 'channel'),
    ],

    // Logging
    'logging' => [
        'enabled' => env('CHANNEL_LOGGING_ENABLED', true),
        'level' => env('CHANNEL_LOG_LEVEL', 'info'),
        'channel' => env('CHANNEL_LOG_CHANNEL', 'channel'),
    ],

    // Monitoring
    'monitoring' => [
        'enabled' => env('CHANNEL_MONITORING_ENABLED', true),
        'metrics' => [
            'connections' => true,
            'messages' => true,
            'errors' => true,
            'latency' => true,
        ],
    ],
];