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
        'route' => env('CHANNEL_AUTH_ROUTE', '/broadcasting/auth'),
    ],
];