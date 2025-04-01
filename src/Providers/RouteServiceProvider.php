<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Routing\Router;
use IronFlow\Core\Providers\ServiceProvider;

/**
 * Fournisseur de services pour le système de routage
 * 
 * Ce service provider initialise et configure le router de l'application
 * et charge les fichiers de définition des routes.
 */
class RouteServiceProvider extends ServiceProvider
{
   /**
    * Enregistre les services liés au routage
    *
    * @return void
    */
   public function register(): void
   {
      $this->app->singleton('router', function ($app) {
         return new Router();
      });
   }

   /**
    * Configure le système de routage après son enregistrement
    *
    * @return void
    */
   public function boot(): void
   {
      // Chargement des routes
      $this->loadRoutes();
   }

   /**
    * Charge les fichiers de définition des routes
    *
    * @return void
    */
   protected function loadRoutes(): void
   {
      $router = $this->app['router'];

      // Chargement des routes web
      if (file_exists($this->app->getBasePath() . '/routes/web.php')) {
         require $this->app->getBasePath() . '/routes/web.php';
      }

      // Chargement des routes API
      if (file_exists($this->app->getBasePath() . '/routes/api.php')) {
         require $this->app->getBasePath() . '/routes/api.php';
      }
   }
}
