<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de channel
    |--------------------------------------------------------------------------
    |
    | Cette configuration définit les paramètres du système de channel d'IronFlow,
    | notamment le provider par défaut et les options des différents providers.
    |
    */

    // Provider par défaut
    'default' => env('CHANNEL_PROVIDER', 'websocket'),

    // Configuration des providers
    'providers' => [
        'websocket' => [
            'host' => env('WEBSOCKET_HOST', '127.0.0.1'),
            'port' => env('WEBSOCKET_PORT', 8080),
            'path' => env('WEBSOCKET_PATH', '/socket'),
            'secure' => env('WEBSOCKET_SECURE', false),
            'timeout' => env('WEBSOCKET_TIMEOUT', 30),
            'max_reconnect_attempts' => env('WEBSOCKET_MAX_RECONNECT_ATTEMPTS', 5),
            'reconnect_interval' => env('WEBSOCKET_RECONNECT_INTERVAL', 1000),
            'heartbeat_interval' => env('WEBSOCKET_HEARTBEAT_INTERVAL', 30000),
            'ping_timeout' => env('WEBSOCKET_PING_TIMEOUT', 5000),
        ],
        
        'pusher' => [
            'app_id' => env('PUSHER_APP_ID'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
            'encrypted' => env('PUSHER_APP_ENCRYPTED', true),
        ],
        
        'socketio' => [
            'host' => env('SOCKETIO_HOST', '127.0.0.1'),
            'port' => env('SOCKETIO_PORT', 6001),
            'path' => env('SOCKETIO_PATH', '/socket.io'),
            'namespace' => env('SOCKETIO_NAMESPACE', '/'),
        ],
    ],

    // Durée de vie des messages en cache (secondes)
    'cache_ttl' => env('CHANNEL_CACHE_TTL', 3600),
    
    // Taille maximale des payloads (octets)
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