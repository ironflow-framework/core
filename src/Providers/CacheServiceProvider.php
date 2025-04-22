<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Cache\CacheManager;
use IronFlow\Core\Service\ServiceProvider;
use IronFlow\Support\Facades\Cache;

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
      $this->container->singleton('cache.manager', function ($app): CacheManager {
         return new CacheManager(config('cache'));
      });
   }

   public function boot(): void
   {
      // Configuration du cache
      $manager = $this->container->get('cache.manager');

      // Configuration du driver par défaut
      $manager->setDefaultDriver(config('cache.default'));

      // Configuration de l'instance Hammer avec le driver par défaut
      Cache::getInstance()->setDriver($manager->driver());
   }
}
