<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Foundation\ServiceProvider;
use IronFlow\Database\Iron\IronManager;

/**
 * Fournisseur de services pour le système de base de données
 * 
 * Ce service provider initialise et configure le système d'ORM Iron.
 */
class DatabaseServiceProvider extends ServiceProvider
{
   /**
    * Enregistre les services liés à la base de données
    * 
    * @return void
    */
   public function register(): void
   {
      $this->app->singleton('db', function ($app) {
         // Configuration à partir du fichier de configuration
         return new IronManager();
      });
   }

   public function boot(): void
   {
      // Configuration de la connexion par défaut
      $this->app['db']->connection();
   }
}
