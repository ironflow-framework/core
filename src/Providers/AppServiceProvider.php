<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Core\Providers\ServiceProvider;

/**
 * Fournisseur de services principal de l'application
 * 
 * Ce service provider initialise les configurations de base
 * et les fonctionnalités principales de l'application.
 */
class AppServiceProvider extends ServiceProvider
{
   /**
    * Enregistre les services principaux de l'application
    *
    * @return void
    */
   public function register(): void
   {
      $this->app->singleton('app', function ($app) {
         return $app;
      });
   }

   /**
    * Configure les services principaux après leur enregistrement
    *
    * @return void
    */
   public function boot(): void
   {
      // Configuration de base de l'application
      date_default_timezone_set($this->app['config']['timezone']);
      setlocale(LC_ALL, $this->app['config']['locale']);
   }
}
