<?php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Database\Factories\Factory;
use Faker\Generator;

class ProductFactory extends Factory
{
   /**
    * Le modèle associé à cette factory
    *
    * @var string
    */
   protected string $model = Product::class;

   /**
    * Définit les attributs par défaut du modèle
    *
    * @param Generator $faker
    * @return array
    */
   public function definition(Generator $faker): array
   {
      return [
         'name' => $faker->words(3, true),
         'description' => $faker->paragraph,
         'price' => $faker->randomFloat(2, 5, 1000),
         'stock' => $faker->numberBetween(0, 100),
         'category_id' => Category::factory()->create()->id,
         'is_active' => $faker->boolean(80),
         'created_at' => $faker->dateTimeThisYear,
         'updated_at' => $faker->dateTimeThisYear,
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
