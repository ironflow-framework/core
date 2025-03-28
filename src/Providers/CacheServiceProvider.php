<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Hammer\HammerManager;
class CacheServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('cache', function ($app) {
         return new HammerManager($app['config']['cache']);
      });
   }

   public function boot(): void
   {
      // Configuration du cache
      $cache = $this->app['cache'];

      // Configuration du driver par défaut
      $cache->setDefaultDriver($this->app['config']['cache']['default']);
   }
}
