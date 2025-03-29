<?php

namespace IronFlow\Support\Security;

/**
 * Classe utilitaire pour le hachage de données
 */
class Hasher
{
   /**
    * Hache une valeur avec SHA-256
    * 
    * @param string $value
    * @return string
    */
   public static function hash(string $value): string
   {
      return hash('sha256', $value);
   }

   /**
    * Vérifie qu'une valeur correspond à un hash
    * 
    * @param string $value
    * @param string $hash
    * @return bool
    */
   public static function verify(string $value, string $hash): bool
   {
      return hash_equals($hash, self::hash($value));
   }
}
