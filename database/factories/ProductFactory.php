<?php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Database\Factories\Factory;

class ProductFactory extends Factory
{
   /**
    * Le modèle associé à cette factory
    *
    * @var string
    */
   protected string $model = Product::class;

   /**
    * Configure les états disponibles pour cette factory
    */
   protected function configure(): void
   {
      $this->states = [
         'featured' => function () {
            return [
               'featured' => true,
               'stock' => $this->faker->numberBetween(10, 100),
            ];
         },
         'out_of_stock' => function () {
            return [
               'stock' => 0,
               'status' => 'unavailable',
            ];
         },
      ];
   }

   /**
    * Définit les attributs par défaut pour le modèle Product
    */
   public function defineDefaults(): void
   {
      $this->defaultAttributes = [
         'name' => $this->faker->words(3, true),
         'description' => $this->faker->paragraph,
         'price' => $this->faker->randomFloat(2, 5, 1000),
         'stock' => $this->faker->numberBetween(1, 50),
         'featured' => false,
         'status' => 'available',
         'category_id' => Category::factory()->create()->id,
         'is_active' => $this->faker->boolean(80),
         'created_at' => $this->faker->dateTimeThisYear,
         'updated_at' => $this->faker->dateTimeThisYear,
      ];
   }

   /**
    * Indique que le produit est en rupture de stock
    *
    * @return $this
    */
   public function outOfStock(): self
   {
      return $this->withOverrides([
         'stock' => 0,
      ]);
   }

   /**
    * Indique que le produit est en stock limité
    *
    * @return $this
    */
   public function lowStock(): self
   {
      return $this->withOverrides([
         'stock' => 5,
      ]);
   }

   /**
    * Indique que le produit est en promotion
    *
    * @return $this
    */
   public function onSale(): self
   {
      return $this->withOverrides([
         'price' => 29.99,
      ]);
   }

   /**
    * Indique que le produit est inactif
    *
    * @return $this
    */
   public function inactive(): self
   {
      return $this->withOverrides([
         'is_active' => false,
      ]);
   }
}
