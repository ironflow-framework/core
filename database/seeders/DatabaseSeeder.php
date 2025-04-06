<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Factories\UserFactory;
use IronFlow\Database\Seeders\Seeder;

/**
 * Exécute les seeders de la base de données
 *
 * @return void
 */
class DatabaseSeeder extends Seeder
{
   /**
    * Exécute les seeders de la base de données
    *
    * @return void
    */
   public function run(): void
   {
      $this->beginTransaction();

      try {
         // Création des utilisateurs
         $userFactory = new UserFactory();
         
         // Admin
         $userFactory->make([
            'name' => 'Admin',
            'email' => 'admin@example.com',
         ], 'admin');

         // Utilisateurs standards
         $userFactory->createMany(10);

         // Utilisateurs non vérifiés
         $userFactory->createMany(5, [], 'unverified');

         $seeders = [
            CategorySeeder::class,
            ProductSeeder::class,
         ];

         foreach ($seeders as $seeder) {
            $instance = new $seeder();
            $instance->run();
         }

         $this->commit();
      } catch (\Exception $e) {
         $this->rollback();
         throw $e;
      }
   }
}
