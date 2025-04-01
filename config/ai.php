<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Services d'IA
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient la configuration pour les différents services d'IA
    | intégrés dans l'application.
    |
    */

    'default' => env('AI_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],
        
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'base_uri' => env('ANTHROPIC_BASE_URI', 'https://api.anthropic.com'),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        ],
        
        'gemini' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'model' => env('GOOGLE_AI_MODEL', 'gemini-1.5-pro'),
            'base_uri' => env('GOOGLE_AI_BASE_URI', 'https://generativelanguage.googleapis.com'),
            'timeout' => env('GOOGLE_AI_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres par défaut
    |--------------------------------------------------------------------------
    |
    | Ces paramètres seront utilisés comme valeurs par défaut pour les appels aux API.
    |
    */
    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'streaming' => false,
    ],
];