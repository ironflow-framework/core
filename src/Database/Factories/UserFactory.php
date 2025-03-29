<?php

declare(strict_types=1);

namespace IronFlow\Database\Factories;

use App\Models\User;

use Faker\Generator as FakerGenerator;

/**
 * Factory pour le modèle User
 */
class UserFactory extends Factory
{
   /**
    * Modèle associé à cette factory
    *
    * @var string
    */
   protected string $model = User::class;

   /**
    * Définit les attributs par défaut pour le modèle User
    *
    * @return array
    */
   public function definition(FakerGenerator $faker): array
   {
      return [
         'name' => $this->faker->name(),
         'email' => $this->faker->unique()->safeEmail(),
         'email_verified_at' => $this->faker->dateTime(),
         'password' => password_hash('password', PASSWORD_BCRYPT),
         'phone' => $this->faker->phoneNumber(),
         'address' => $this->faker->address(),
         'created_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
         'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
      ];
   }

   /**
    * État pour les utilisateurs administrateurs
    *
    * @return $this
    */
   public function admin(): self
   {
      return $this->state('admin');
   }

   /**
    * État pour les utilisateurs sans vérification d'email
    *
    * @return $this
    */
   public function unverified(): self
   {
      return $this->state('unverified');
   }

   /**
    * États disponibles pour cette factory
    */
   protected array $states = [
      'admin' => function () {
         return [
            'role' => 'admin',
            'is_admin' => true,
         ];
      },
      'unverified' => function () {
         return [
            'email_verified_at' => null,
         ];
      },
   ];
}
