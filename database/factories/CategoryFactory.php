<?php

declare(strict_types=1);

namespace App\Database\Factories;

use App\Models\Category;
use IronFlow\Database\Factories\Factory;
use Faker\Generator;

class CategoryFactory extends Factory
{
   /**
    * Le modèle associé à cette factory
    *
    * @var string
    */
   protected string $model = Category::class;

   /**
    * Définit les attributs par défaut du modèle
    *
    * @param Generator $faker
    * @return array
    */
   public function definition(Generator $faker): array
   {
      return [
         'name' => $faker->word,
         'description' => $faker->sentence,
         'parent_id' => null,
         'is_active' => $faker->boolean(80),
         'type' => $faker->randomElement(['product', 'service']),
         'created_at' => $faker->dateTimeThisYear,
         'updated_at' => $faker->dateTimeThisYear,
      ];
   }

   /**
    * Indique que la catégorie est inactive
    *
    * @return $this
    */
   public function inactive(): self
   {
      return $this->withOverrides([
         'is_active' => false,
      ]);
   }

   /**
    * Indique que la catégorie est de type produit
    *
    * @return $this
    */
   public function product(): self
   {
      return $this->withOverrides([
         'type' => 'product',
      ]);
   }

   /**
    * Indique que la catégorie est de type service
    *
    * @return $this
    */
   public function service(): self
   {
      return $this->withOverrides([
         'type' => 'service',
      ]);
   }
}
