<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Support\ServiceProvider;
use IronFlow\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('router', function ($app) {
         return new Router();
      });
   }

   public function boot(): void
   {
      // Chargement des routes
      $this->loadRoutes();
   }

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
