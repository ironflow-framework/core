<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\View\TwigView;
use IronFlow\Core\Providers\ServiceProvider;

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
         return new TwigView(view_path() ?? $app->getBasePath() . '/resources/views');
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
      $view = $this->app->getContainer()->get('view');

      $view->addGlobal('APP_LANG', config('app.locale'));
      $view->addGlobal('APP_VERSION', config('app.version', '1.0.0'));

      $view->addFunction('url', function (string $path, array $parameters = []): string {
         $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
         $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
         $path = trim($path, '/');
         $query = !empty($parameters) ? '?' . http_build_query($parameters) : '';
         return $baseUrl . '/' . $path . $query;
      });

      $view->addFunction('asset', function (string $path): string {
         return '/assets/' . ltrim($path, '/');
      });

      $view->addFunction('route', function ($name, $parameters = []) {
         $path = str_replace('.', '/', $name);
         return $this->app->getContainer()->get('router')->url($path, $parameters);
      });
   }
}
