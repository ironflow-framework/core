<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\View\TwigView;
use IronFlow\Foundation\ServiceProvider;

/**
 * Fournisseur de services pour le système de vues
 * 
 * Ce service provider initialise et configure le système de templates Twig.
 */
class ViewServiceProvider extends ServiceProvider
{
   /**
    * Enregistre les services liés aux vues
    *
    * @return void
    */
   public function register(): void
   {
      $this->app->singleton('view', function ($app) {
         return new TwigView($app->getBasePath() . '/resources/views');
      });
   }

   /**
    * Configure le système de vues après son enregistrement
    *
    * @return void
    */
   public function boot(): void
   {
      // Configuration des vues
      $view = $this->app['view'];

      // Ajout des fonctions globales
      $view->addFunction('asset', function ($path) {
         return '/assets/' . ltrim($path, '/');
      });

      $view->addFunction('route', function ($name, $parameters = []) {
         return $this->app['router']->url($name, $parameters);
      });
   }
}
