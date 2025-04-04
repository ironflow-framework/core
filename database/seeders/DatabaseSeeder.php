<?php

declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use IronFlow\Database\Seeder\Seeder;

class DatabaseSeeder extends Seeder
{
   /**
    * Exécute les seeders de la base de données
    *
    * @return void
    */
   public function run(): void
   {
      $seeders = [
         CategorySeeder::class,
         ProductSeeder::class,
      ];

      foreach ($seeders as $seeder) {
         $instance = new $seeder();
         $instance->run();
      }
   }
}
