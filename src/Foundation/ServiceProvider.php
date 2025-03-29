<?php

declare(strict_types=1);

namespace IronFlow\Foundation;

use IronFlow\Foundation\Application;

/**
 * Classe de base pour tous les fournisseurs de services
 */
abstract class ServiceProvider
{
   /**
    * Application IronFlow
    */
   protected Application $app;

   /**
    * Constructeur
    */
   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   /**
    * Enregistre les services fournis par ce fournisseur
    * Cette méthode doit être implémentée par toutes les classes filles
    */
   abstract public function register(): void;

   /**
    * Démarre les services fournis par ce fournisseur
    * Cette méthode peut être surchargée pour effectuer des actions après l'enregistrement
    */
   public function boot(): void
   {
      // Les providers peuvent surcharger cette méthode si nécessaire
   }

   /**
    * Récupère un service depuis le container
    */
   protected function make(string $abstract): mixed
   {
      return $this->app->make($abstract);
   }

   /**
    * Publie des fichiers depuis le fournisseur vers l'application
    */
   protected function publishes(array $paths, string $group = null): void
   {
      // TODO: Implement publishes() method.
   }

   /**
    * Obtient le chemin du package pour la publication
    */
   protected function packagePath(string $path = ''): string
   {
      $reflector = new \ReflectionClass($this);
      $packageDirectory = dirname($reflector->getFileName());

      return $packageDirectory . ($path ? DIRECTORY_SEPARATOR . $path : '');
   }
}
