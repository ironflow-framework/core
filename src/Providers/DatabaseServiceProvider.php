<?php

declare(strict_types=1);

namespace IronFlow\Providers;

use IronFlow\Core\Service\ServiceProvider;
use IronFlow\Database\Iron\IronManager;
use IronFlow\Database\Connection;

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
      $this->container->singleton('db.manager', function (): IronManager {
         return new IronManager();
      });
   }

   /**
    * Configure le système de base de données
    * 
    * @return void
    */
   public function boot(): void
   {
      // Initialisation de la connexion par défaut
      $manager = $this->container->get('db.manager');
      $manager->connection();
   }
}
