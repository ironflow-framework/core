<?php

namespace IronFlow\Support\Security;

/**
 * Classe utilitaire pour le hachage de mots de passe
 */
class PasswordHasher
{
   /**
    * Hache un mot de passe avec password_hash (bcrypt par défaut)
    * 
    * @param string $password
    * @param array $options
    * @return string
    */
   public static function hash(string $password, array $options = []): string
   {
      $algorithm = $options['algorithm'] ?? PASSWORD_DEFAULT;
      $cost = $options['cost'] ?? 10;

      return password_hash($password, $algorithm, ['cost' => $cost]);
   }

   /**
    * Vérifie qu'un mot de passe correspond à un hash
    * 
    * @param string $password
    * @param string $hash
    * @return bool
    */
   public static function verify(string $password, string $hash): bool
   {
      return password_verify($password, $hash);
   }

   /**
    * Vérifie si un hachage de mot de passe doit être recalculé
    * 
    * @param string $hash
    * @param array $options
    * @return bool
    */
   public static function needsRehash(string $hash, array $options = []): bool
   {
      $algorithm = $options['algorithm'] ?? PASSWORD_DEFAULT;
      $cost = $options['cost'] ?? 10;

      return password_needs_rehash($hash, $algorithm, ['cost' => $cost]);
   }
}
