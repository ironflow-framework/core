<?php

declare(strict_types=1);

namespace IronFlow\Providers;
use IronFlow\Database\DatabaseManager;

class DatabaseServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('db', function ($app) {
         return new DatabaseManager($app['config']['database']);
      });
   }

   public function boot(): void
   {
      // Configuration de la connexion par défaut
      $this->app['db']->connection();
   }
}
