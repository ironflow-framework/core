<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use DateTime;
use IronFlow\Database\Factories\Factory;

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
    * États disponibles pour cette factory
    */
   protected array $states = [];

   /**
    * Initialisation des états
    */
   public function configure(): void
   {
      $this->states = [
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

   /**
    * Définit les attributs par défaut pour le modèle User
    */
   public function defineDefaults(): void
   {
      $this->defaultAttributes = [
         'name' => $this->faker->name(),
         'email' => $this->faker->unique()->safeEmail(),
         'password' => password_hash('password', PASSWORD_BCRYPT),
         'email_verified_at' => new DateTime(),
         'remember_token' => bin2hex(random_bytes(5)),
         'role' => 'user',
         'is_admin' => false,
         'phone' => $this->faker->phoneNumber(),
         'address' => $this->faker->address(),
         'created_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
         'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
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
}
