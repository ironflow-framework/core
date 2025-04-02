<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Database\SqlInjectionProtection as SqlInjectionProtectionInstance;

class SqlInjectionProtection extends Facade
{
   /**
    * Récupère l'instance de la protection contre les injections SQL
    */
   protected static function getFacadeInstance(): SqlInjectionProtectionInstance
   {
      return new SqlInjectionProtectionInstance();
   }

   /**
    * Vérifie si une chaîne contient des injections SQL potentielles
    */
   public static function containsInjection(string $value): bool
   {
      return SqlInjectionProtectionInstance::containsInjection($value);
   }

   /**
    * Nettoie une chaîne pour la rendre sûre
    */
   public static function sanitize(string $value): string
   {
      return SqlInjectionProtectionInstance::sanitize($value);
   }

   /**
    * Vérifie si un tableau contient des injections SQL potentielles
    */
   public static function containsInjectionInArray(array $array): bool
   {
      return SqlInjectionProtectionInstance::containsInjectionInArray($array);
   }

   /**
    * Nettoie un tableau pour le rendre sûr
    */
   public static function sanitizeArray(array $array): array
   {
      return SqlInjectionProtectionInstance::sanitizeArray($array);
   }
}
