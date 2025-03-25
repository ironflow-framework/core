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
      'App' => IronFlow\Support\Facades\App::class,
      'Route' => IronFlow\Support\Facades\Route::class,
      'DB' => IronFlow\Support\Facades\DB::class,
      'View' => IronFlow\Support\Facades\View::class,
      'Cache' => IronFlow\Support\Facades\Cache::class,
   ],

   'fallback_locale' => 'en',
   'faker_locale' => 'fr_FR',
   'cipher' => 'AES-256-CBC',
];
