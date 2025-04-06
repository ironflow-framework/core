<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use IronFlow\Database\Seeders\Seeder;

class CategorySeeder extends Seeder
{
   /**
    * Exécute le seeder des catégories
    *
    * @return void
    */
   public function run(): void
   {
      // Création de catégories de produits
      $electronics = Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Électronique',
            'description' => 'Produits électroniques et gadgets',
         ]);

      $clothing = Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Vêtements',
            'description' => 'Vêtements et accessoires',
         ]);

      $books = Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Livres',
            'description' => 'Livres et publications',
         ]);

      // Création de catégories de services
      $consulting = Category::factory()
         ->withOverrides(['type' => 'service'])
         ->create([
            'name' => 'Conseil',
            'description' => 'Services de conseil professionnel',
         ]);

      $maintenance = Category::factory()
         ->withOverrides(['type' => 'service'])
         ->create([
            'name' => 'Maintenance',
            'description' => 'Services de maintenance et réparation',
         ]);

      // Création de sous-catégories
      Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Smartphones',
            'description' => 'Téléphones mobiles et accessoires',
            'parent_id' => $electronics->id,
         ]);

      Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Ordinateurs',
            'description' => 'Ordinateurs portables et de bureau',
            'parent_id' => $electronics->id,
         ]);

      Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Homme',
            'description' => 'Vêtements pour hommes',
            'parent_id' => $clothing->id,
         ]);

      Category::factory()
         ->withOverrides(['type' => 'product'])
         ->create([
            'name' => 'Femme',
            'description' => 'Vêtements pour femmes',
            'parent_id' => $clothing->id,
         ]);

      // Création de catégories supplémentaires
      Category::factory()->count(5)->create();
   }
}
