<?php

return [
   /*
    |--------------------------------------------------------------------------
    | Route Caching
    |--------------------------------------------------------------------------
    |
    | Cette option permet de configurer le cache des routes pour améliorer
    | les performances.
    |
    */
   'cache' => [
      'enabled' => env('ROUTE_CACHE_ENABLED', true),
      'lifetime' => env('ROUTE_CACHE_LIFETIME', 3600),
   ],

   /*
    |--------------------------------------------------------------------------
    | Middleware Groups
    |--------------------------------------------------------------------------
    |
    | Configuration des groupes de middleware par défaut.
    |
    */
   'middleware_groups' => [
      'web' => [
         \App\Http\Middleware\EncryptCookies::class,
         \App\Http\Middleware\VerifyCsrfToken::class,
         \App\Http\Middleware\StartSession::class,
      ],
      'api' => [
         'throttle:60,1',
         \App\Http\Middleware\ApiResponse::class,
      ],
   ],

   /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    |
    | Configuration du binding automatique des modèles dans les routes.
    |
    */
   'model_binding' => [
      'enabled' => true,
      'cache' => true,
   ],

   /*
    |--------------------------------------------------------------------------
    | Route Parameters
    |--------------------------------------------------------------------------
    |
    | Configuration des paramètres de route par défaut.
    |
    */
   'parameters' => [
      'id' => '[0-9]+',
      'slug' => '[a-z0-9-]+',
   ],
];
