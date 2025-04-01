<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Core\Providers\ServiceProvider;
use IronFlow\Cache\Hammer\HammerManager;

/**
 * Fournisseur de services pour le système de cache
 * 
 * Ce service provider initialise et configure le système de cache Hammer.
 */
class CacheServiceProvider extends ServiceProvider
{
   /**
    * Enregistre les services liés au cache
    * 
    * @return void
    */
   public function register(): void
   {
      $this->app->singleton('cache', function ($app): HammerManager {
         return new HammerManager(config('cache'));
      });
   }

   public function boot(): void
   {
      // Configuration du cache
      $cache = $this->app->getContainer()->get('cache');

      // Configuration du driver par défaut
      $cache->setDefaultDriver(config('cache.default'));
   }
}
