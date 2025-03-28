<?php

declare(strict_types=1);

namespace IronFlow\Providers;
class AppServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('app', function ($app) {
         return $app;
      });
   }

   public function boot(): void
   {
      // Configuration de base de l'application
      date_default_timezone_set($this->app['config']['timezone']);
      setlocale(LC_ALL, $this->app['config']['locale']);
   }
}
