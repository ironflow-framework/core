<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Security;

class SqlInjectionProtection
{
   /**
    * Liste des mots-clés SQL dangereux
    */
   private const DANGEROUS_KEYWORDS = [
      'UNION',
      'SELECT',
      'INSERT',
      'UPDATE',
      'DELETE',
      'DROP',
      'TRUNCATE',
      'ALTER',
      'CREATE',
      'EXEC',
      'EXECUTE',
      'DECLARE',
      'WAITFOR',
      'DELAY',
      'SHUTDOWN',
      '--',
      ';',
      '/*',
      '*/',
      '@@',
      'xp_',
      'sp_',
      '0x',
      '0b',
      '0o',
      '0d',
      '0h',
      '0i',
      '0j',
      '0k',
      '0l',
      '0m',
      '0n',
      '0o',
      '0p',
      '0q',
      '0r',
      '0s',
      '0t',
      '0u',
      '0v',
      '0w',
      '0x',
      '0y',
      '0z',
   ];

   /**
    * Liste des caractères spéciaux dangereux
    */
   private const DANGEROUS_CHARS = [
      "'",
      '"',
      '`',
      ';',
      '--',
      '/*',
      '*/',
      '@@',
      '0x',
      '0b',
      '0o',
      '0d',
      '0h',
      '0i',
      '0j',
      '0k',
      '0l',
      '0m',
      '0n',
      '0o',
      '0p',
      '0q',
      '0r',
      '0s',
      '0t',
      '0u',
      '0v',
      '0w',
      '0x',
      '0y',
      '0z',
   ];

   /**
    * Vérifie si une chaîne contient des injections SQL potentielles
    */
   public static function containsInjection(string $value): bool
   {
      $value = strtoupper($value);

      // Vérifie les mots-clés dangereux
      foreach (self::DANGEROUS_KEYWORDS as $keyword) {
         if (strpos($value, $keyword) !== false) {
            return true;
         }
      }

      // Vérifie les caractères spéciaux dangereux
      foreach (self::DANGEROUS_CHARS as $char) {
         if (strpos($value, $char) !== false) {
            return true;
         }
      }

      return false;
   }

   /**
    * Nettoie une chaîne pour la rendre sûre
    */
   public static function sanitize(string $value): string
   {
      // Supprime les caractères spéciaux dangereux
      $value = str_replace(self::DANGEROUS_CHARS, '', $value);

      // Échappe les guillemets
      $value = addslashes($value);

      return $value;
   }

   /**
    * Vérifie si un tableau contient des injections SQL potentielles
    */
   public static function containsInjectionInArray(array $array): bool
   {
      foreach ($array as $value) {
         if (is_string($value) && self::containsInjection($value)) {
            return true;
         }
         if (is_array($value) && self::containsInjectionInArray($value)) {
            return true;
         }
      }

      return false;
   }

   /**
    * Nettoie un tableau pour le rendre sûr
    */
   public static function sanitizeArray(array $array): array
   {
      $result = [];

      foreach ($array as $key => $value) {
         if (is_string($value)) {
            $result[$key] = self::sanitize($value);
         } elseif (is_array($value)) {
            $result[$key] = self::sanitizeArray($value);
         } else {
            $result[$key] = $value;
         }
      }

      return $result;
   }
}
