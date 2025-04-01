<?php

declare(strict_types=1);

namespace IronFlow\Core\Providers;

use IronFlow\Core\Application;

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
   protected function publishes(array $paths, ?string $group = null): void
   {
      if (empty($paths)) {
         return;
      }

      $publishPath = $this->app->basePath('vendor/publishes');

      if (!is_dir($publishPath)) {
         mkdir($publishPath, 0755, true);
      }

      foreach ($paths as $from => $to) {
         $fromPath = $this->packagePath($from);
         $toPath = $publishPath . DIRECTORY_SEPARATOR . $to;

         if (is_dir($fromPath)) {
            $this->copyDirectory($fromPath, $toPath);
         } else {
            $this->copyFile($fromPath, $toPath);
         }
      }
   }

   /**
    * Copie un répertoire et son contenu
    */
   private function copyDirectory(string $from, string $to): void
   {
      if (!is_dir($to)) {
         mkdir($to, 0755, true);
      }

      $files = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS),
         \RecursiveIteratorIterator::SELF_FIRST
      );

      foreach ($files as $file) {
         if ($file->isDir()) {
            mkdir($to . DIRECTORY_SEPARATOR . $file->getRelativePathname(), 0755, true);
         } else {
            copy($file->getPathname(), $to . DIRECTORY_SEPARATOR . $file->getRelativePathname());
         }
      }
   }

   /**
    * Copie un fichier
    */
   private function copyFile(string $from, string $to): void
   {
      $directory = dirname($to);
      if (!is_dir($directory)) {
         mkdir($directory, 0755, true);
      }

      copy($from, $to);
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
