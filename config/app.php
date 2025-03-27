<?php

declare(strict_types=1);

return [
   'name' => 'IronFlow',
   'env' => env('APP_ENV', 'production'),
   'debug' => env('APP_DEBUG', false),
   'url' => env('APP_URL', 'http://localhost'),
   'timezone' => 'Europe/Paris',
   'locale' => 'fr',
   'key' => env('APP_KEY'),
   'version' => env('APP_VERSION', '1.0.0'),

   'providers' => [
      // Providers système
      IronFlow\Providers\AppServiceProvider::class,
      IronFlow\Providers\RouteServiceProvider::class,
      IronFlow\Providers\DatabaseServiceProvider::class,
      IronFlow\Providers\ViewServiceProvider::class,
      IronFlow\Providers\CacheServiceProvider::class,
   ],

   'aliases' => [
      'App' => IronFlow\Application\Application::class,
      'Route' => IronFlow\Routing\Router::class,
      'DB' => IronFlow\Database\Connection::class,
      'View' => IronFlow\View\TwigView::class,
      'Cache' => IronFlow\Cache\Cache::class,
   ],

   'fallback_locale' => 'en',
   'faker_locale' => 'fr_FR',
   'cipher' => 'AES-256-CBC',
];
