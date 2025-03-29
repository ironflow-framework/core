<?php

declare(strict_types=1);

namespace IronFlow\Support\Utils;

/**
 * Classe utilitaire pour la manipulation de chaînes de caractères
 */
class Str
{
   /**
    * Convertit une chaîne en titre
    */
   public static function title(string $string): string
   {
      return ucwords(str_replace('_', ' ', $string));
   }

   /**
    * Convertit une chaîne en slug pour les URL
    */
   public static function slug(string $string): string
   {
      return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
   }

   /**
    * Convertit une chaîne en StudlyCase
    */
   public static function studly(string $string): string
   {
      $words = explode(' ', str_replace(['-', '_'], ' ', $string));
      $studlyWords = array_map('ucfirst', $words);
      return implode('', $studlyWords);
   }

   /**
    * Convertit une chaîne en kebab-case
    */
   public static function kebab(string $string): string
   {
      return str_replace('_', '-', static::snake($string));
   }

   /**
    * Convertit une chaîne en snake_case
    */
   public static function snake(string $string): string
   {
      $value = preg_replace('/\s+/u', '_', $string);
      $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);
      return mb_strtolower($value);
   }

   /**
    * Convertit une chaîne en camelCase
    */
   public static function camel(string $string): string
   {
      return lcfirst(static::studly($string));
   }

   /**
    * Échappe une chaîne pour l'affichage HTML
    */
   public static function escape(string $string): string
   {
      return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
   }

   /**
    * Décode les entités HTML dans une chaîne
    */
   public static function unescape(string $string): string
   {
      return htmlspecialchars_decode($string, ENT_QUOTES);
   }

   /**
    * Vérifie si une chaîne commence par une sous-chaîne
    */
   public static function startsWith(string $string, $needles): bool
   {
      foreach ((array) $needles as $needle) {
         if ($needle !== '' && strncmp($string, $needle, mb_strlen($needle)) === 0) {
            return true;
         }
      }
      return false;
   }

   /**
    * Vérifie si une chaîne se termine par une sous-chaîne
    */
   public static function endsWith(string $string, $needles): bool
   {
      foreach ((array) $needles as $needle) {
         if ($needle !== '' && mb_substr($string, -mb_strlen($needle)) === $needle) {
            return true;
         }
      }
      return false;
   }

   /**
    * Vérifie si une chaîne contient une sous-chaîne
    */
   public static function contains(string $haystack, $needles): bool
   {
      foreach ((array) $needles as $needle) {
         if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
            return true;
         }
      }
      return false;
   }

   /**
    * Remplace une sous-chaîne dans une chaîne
    */
   public static function replace(string $string, string $needle, string $replacement): string
   {
      return str_replace($needle, $replacement, $string);
   }

   /**
    * Supprime une sous-chaîne d'une chaîne
    */
   public static function remove(string $string, string $needle): string
   {
      return str_replace($needle, '', $string);
   }

   /**
    * Supprime plusieurs sous-chaînes d'une chaîne
    */
   public static function removeAll(string $string, array $needles): string
   {
      return str_replace($needles, '', $string);
   }

   /**
    * Supprime tous les espaces d'une chaîne
    */
   public static function removeAllSpaces(string $string): string
   {
      return preg_replace('/\s+/', '', $string);
   }

   /**
    * Supprime tous les espaces et les espaces en début/fin d'une chaîne
    */
   public static function removeAllSpacesAndTrim(string $string): string
   {
      return trim(preg_replace('/\s+/', '', $string));
   }

   /**
    * Convertit une chaîne en minuscules
    */
   public static function lower(string $string): string
   {
      return strtolower($string);
   }

   /**
    * Convertit une chaîne en majuscules
    */
   public static function upper(string $string): string
   {
      return strtoupper($string);
   }

   /**
    * Capitalise chaque mot d'une chaîne
    */
   public static function capitalize(string $string): string
   {
      return ucwords($string);
   }

   /**
    * Inverse une chaîne
    */
   public static function reverse(string $string): string
   {
      return strrev($string);
   }

   /**
    * Retourne la longueur d'une chaîne
    */
   public static function length(string $string): int
   {
      return strlen($string);
   }

   /**
    * Génère une chaîne aléatoire
    */
   public static function random(int $length = 16): string
   {
      $string = '';

      while (($len = strlen($string)) < $length) {
         $size = $length - $len;
         $bytes = random_bytes($size);
         $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
      }

      return $string;
   }

   /**
    * Génère une chaîne aléatoire de chiffres
    */
   public static function randomNumber(int $length = 16): string
   {
      return substr(str_shuffle(str_repeat($x = '0123456789', (int) ceil($length / strlen($x)))), 1, $length);
   }

   /**
    * Tronque une chaîne à la longueur spécifiée
    */
   public static function limit(string $value, int $limit = 100, string $end = '...'): string
   {
      if (mb_strlen($value) <= $limit) {
         return $value;
      }
      return mb_substr($value, 0, $limit) . $end;
   }
}
