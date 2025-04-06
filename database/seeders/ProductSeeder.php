<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Database\Seeders\Seeder;

class ProductSeeder extends Seeder
{
   /**
    * Exécute le seeder des produits
    *
    * @return void
    */
   public function run(): void
   {
      // Récupération des catégories
      $electronics = Category::where('name', 'Électronique')->first();
      $clothing = Category::where('name', 'Vêtements')->first();
      $books = Category::where('name', 'Livres')->first();
      $consulting = Category::where('name', 'Conseil')->first();
      $maintenance = Category::where('name', 'Maintenance')->first();

      // Création de produits pour chaque catégorie
      if ($electronics) {
         Product::factory()->count(10)->create([
            'category_id' => $electronics->id,
         ]);

         // Création de produits en promotion
         Product::factory()->count(3)->withOverrides(['price' => 29.99])->create([
            'category_id' => $electronics->id,
         ]);

         // Création de produits en rupture de stock
         Product::factory()->count(2)->withOverrides(['stock' => 0])->create([
            'category_id' => $electronics->id,
         ]);
      }

      if ($clothing) {
         Product::factory()->count(15)->create([
            'category_id' => $clothing->id,
         ]);

         // Création de produits en stock limité
         Product::factory()->count(5)->withOverrides(['stock' => 5])->create([
            'category_id' => $clothing->id,
         ]);
      }

      if ($books) {
         Product::factory()->count(20)->create([
            'category_id' => $books->id,
         ]);
      }

      if ($consulting) {
         Product::factory()->count(5)->create([
            'category_id' => $consulting->id,
         ]);
      }

      if ($maintenance) {
         Product::factory()->count(5)->create([
            'category_id' => $maintenance->id,
         ]);
      }

      // Création de produits inactifs
      Product::factory()->count(3)->withOverrides(['is_active' => false])->create();

      // Création de produits supplémentaires
      Product::factory()->count(10)->create();
   }
}
