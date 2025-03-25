<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Support\ServiceProvider;
use IronFlow\View\TwigView;

class ViewServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('view', function ($app) {
         return new TwigView($app->getBasePath() . '/resources/views');
      });
   }

   public function boot(): void
   {
      // Configuration des vues
      $view = $this->app['view'];

      // Ajout des fonctions globales
      $view->addFunction('asset', function ($path) {
         return '/assets/' . ltrim($path, '/');
      });

      $view->addFunction('route', function ($name, $parameters = []) {
         // TODO: Implémenter la génération d'URLs pour les routes nommées
         return '/' . $name;
      });
   }
}
