<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Security\PasswordHasher;

/**
 * Façade pour le hachage de mots de passe
 * 
 * @method static string hash(string $password, array $options = [])
 * @method static bool verify(string $password, string $hash)
 * @method static bool needsRehash(string $hash, array $options = [])
 */
class Password
{
   /**
    * Hache un mot de passe
    *
    * @param string $password
    * @param array $options
    * @return string
    */
   public static function hash(string $password, array $options = []): string
   {
      return PasswordHasher::hash($password, $options);
   }

   /**
    * Vérifie un mot de passe
    *
    * @param string $password
    * @param string $hash
    * @return bool
    */
   public static function verify(string $password, string $hash): bool
   {
      return PasswordHasher::verify($password, $hash);
   }

   /**
    * Vérifie si un hash doit être recalculé
    *
    * @param string $hash
    * @param array $options
    * @return bool
    */
   public static function needsRehash(string $hash, array $options = []): bool
   {
      return PasswordHasher::needsRehash($hash, $options);
   }
}
