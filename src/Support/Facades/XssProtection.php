<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Security\XssProtection as XssProtectionInstance;

class XssProtection extends Facade
{
   /**
    * Récupère l'instance de la protection contre les attaques XSS
    */
   protected static function getFacadeInstance(): XssProtectionInstance
   {
      return new XssProtectionInstance();
   }

   /**
    * Vérifie si une chaîne contient des attaques XSS potentielles
    */
   public static function containsXss(string $value): bool
   {
      return XssProtectionInstance::containsXss($value);
   }

   /**
    * Nettoie une chaîne pour la rendre sûre
    */
   public static function sanitize(string $value): string
   {
      return XssProtectionInstance::sanitize($value);
   }

   /**
    * Vérifie si un tableau contient des attaques XSS potentielles
    */
   public static function containsXssInArray(array $array): bool
   {
      return XssProtectionInstance::containsXssInArray($array);
   }

   /**
    * Nettoie un tableau pour le rendre sûr
    */
   public static function sanitizeArray(array $array): array
   {
      return XssProtectionInstance::sanitizeArray($array);
   }
}
