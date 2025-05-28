<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeders;

use IronFlow\Database\Exceptions\SeederException;

/**
 * Gestionnaire des seeders de base de données
 * 
 * Cette classe permet d'exécuter les seeders de base de données
 * de manière centralisée. Elle gère l'ordre d'exécution des seeders
 * et assure que chaque seeder n'est exécuté qu'une seule fois.
 * 
 * @package IronFlow\Database\Seeder
 * @author Aure Dulvresse
 * @version 1.0.0
 */
class SeederManager extends Seeder
{
   protected array $seeders = [];
   protected array $executed = [];
   protected bool $useTransactions = true;

   /**
    * Configure les seeders à exécuter
    */
   protected function configure(): void
   {
      // À surcharger dans les classes enfants
   }

   /**
    * Ajoute un seeder à la liste
    */
   public function add(string $seederClass): self
   {
      $this->seeders[] = $seederClass;
      return $this;
   }

   /**
    * Ajoute plusieurs seeders
    */
   public function addMany(array $seeders): self
   {
      $this->seeders = array_merge($this->seeders, $seeders);
      return $this;
   }

   /**
    * Exécute tous les seeders configurés
    */
   public function run(?\Closure $progressCallback = null): void
   {
      $this->configure();

      if (empty($this->seeders)) {
         return;
      }

      $orderedSeeders = $this->resolveDependencies();

      foreach ($orderedSeeders as $seederClass) {
         $this->runSeeder($seederClass, $progressCallback);
      }
   }

   /**
    * Résout les dépendances entre les seeders
    */
   protected function resolveDependencies(): array
   {
      $orderedSeeders = [];
      $dependencies = [];

      // Collect all seeder dependencies
      foreach ($this->seeders as $seederClass) {
         $seeder = new $seederClass();
         $dependencies[$seederClass] = $seeder->getDependencies();
      }

      // Topological sorting
      while (!empty($dependencies)) {
         $independentSeeders = array_filter($dependencies, function ($dependencies) {
            return empty($dependencies);
         });

         if (empty($independentSeeders)) {
            throw new \LogicException('Circular dependency detected');
         }

         foreach ($independentSeeders as $seederClass => $dependencies) {
            $orderedSeeders[] = $seederClass;
            unset($dependencies[$seederClass]);

            foreach ($dependencies as &$seederDependencies) {
               $seederDependencies = array_diff($seederDependencies, [$seederClass]);
            }
         }
      }
      // Si vous avez besoin de trier les seeders par ordre spécifique,
      $orderedSeeders = [];

      sort($orderedSeeders);
    
      return $orderedSeeders;
   }


   /**
    * Exécute un seeder spécifique
    */
   protected function runSeeder(string $seederClass, ?\Closure $progressCallback = null): void
   {
      if (in_array($seederClass, $this->executed)) {
         return; // Déjà exécuté
      }

      if (!class_exists($seederClass)) {
         throw new SeederException("Seeder class {$seederClass} does not exist");
      }

      $seeder = new $seederClass($this->connection);

      try {
         $seeder->execute($progressCallback);
         $this->executed[] = $seederClass;
      } catch (\Throwable $e) {
         throw new SeederException(
            "Failed to run seeder {$seederClass}: " . $e->getMessage(),
            0,
            $e
         );
      }
   }
}
