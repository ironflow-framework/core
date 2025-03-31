<?php

declare(strict_types=1);

return [
   'name' => env('APP_NAME', 'IronFlow'),
   'env' => env('APP_ENV', 'production'),
   'debug' => env('APP_DEBUG', false),
   'url' => env('APP_URL', 'http://localhost'),
   'timezone' => env('APP_TIMEZONE', 'Europe/Paris'),
   'locale' => env('APP_LOCALE', 'fr'),
   'key' => env('APP_KEY'),
   'version' => env('APP_VERSION', '1.0.0'),

   'providers' => [
      config('services.providers'),
   ],

   'aliases' => [
      'App' => IronFlow\Core\Application::class,
      'Route' => IronFlow\Routing\Router::class,
      'DB' => IronFlow\Database\Connection::class,
      'View' => IronFlow\View\TwigView::class,
      'Cache' => IronFlow\Cache\Hammer\HammerManager::class,
      'Translator' => IronFlow\Support\Facades\Trans::class,
   ],

   'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
   'faker_locale' => env('APP_FAKER_LOCALE', 'fr_FR'),
   'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
];
