<?php

declare(strict_types=1);

namespace Database\Seeder;

use PDO;
use IronFlow\Database\Seeder\Seeder;

/**
 * Seeder principal de la base de données
 * 
 * Ce seeder est le point d'entrée pour l'exécution de tous les seeders du projet.
 * Chaque nouveau seeder doit être ajouté dans la méthode run().
 */
class DatabaseSeeder extends Seeder
{
   /**
    * Exécute les opérations de seeding
    *
    * @return void
    */
   public function run(): void
   {
      // Les seeders seront ajoutés ici
      // Exemple:
      // $this->call(UserSeeder::class);
      // $this->call(ProductSeeder::class);
   }
}
