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
      IronFlow\Providers\TranslationServiceProvider::class,

      // Providers de fonctionnalités
      IronFlow\Payment\PaymentServiceProvider::class,
      IronFlow\Channel\ChannelServiceProvider::class,
      IronFlow\Framework\AI\AIServiceProvider::class,
   ],

   'aliases' => [
      'App' => IronFlow\Foundation\Application::class,
      'Route' => IronFlow\Routing\Router::class,
      'DB' => IronFlow\Database\Connection::class,
      'View' => IronFlow\View\TwigView::class,
      'Cache' => IronFlow\Cache\Hammer\HammerManager::class,
      'Translator' => IronFlow\Support\Translator::class,
   ],

   'fallback_locale' => 'en',
   'faker_locale' => 'fr_FR',
   'cipher' => 'AES-256-CBC',
];
