<?php

namespace IronFlow\Support\Security;

/**
 * Classe pour le hachage des mots de passe
 */
class PasswordHasher
{
   /**
    * Hacher un mot de passe
    */
   public function make(string $value, array $options = []): string
   {
      $algorithm = $options['algorithm'] ?? PASSWORD_ARGON2ID;
      $cost = $options['cost'] ?? null;

      $hash = password_hash($value, $algorithm, array_filter([
         'cost' => $cost,
      ]));

      if ($hash === false) {
         throw new \RuntimeException('Impossible de hacher le mot de passe.');
      }

      return $hash;
   }

   /**
    * Vérifier un mot de passe
    */
   public function check(string $value, string $hashedValue): bool
   {
      if (strlen($hashedValue) === 0) {
         return false;
      }

      return password_verify($value, $hashedValue);
   }

   /**
    * Vérifier si un hash a besoin d'être refait
    */
   public function needsRehash(string $hashedValue, array $options = []): bool
   {
      $algorithm = $options['algorithm'] ?? PASSWORD_ARGON2ID;

      return password_needs_rehash($hashedValue, $algorithm, $options);
   }
}