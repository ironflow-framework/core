<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Security\Hasher;

/**
 * Façade pour le Hasher
 * 
 * @method static string hash(string $value)
 * @method static bool verify(string $value, string $hash)
 */
class Hash
{
   /**
    * Hash une valeur
    *
    * @param string $value
    * @return string
    */
   public static function hash(string $value): string
   {
      return Hasher::hash($value);
   }

   /**
    * Vérifie un hash
    *
    * @param string $value
    * @param string $hash
    * @return bool
    */
   public static function verify(string $value, string $hash): bool
   {
      return Hasher::verify($value, $hash);
   }
}
