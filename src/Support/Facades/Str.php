<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Str as StrUtils;

/**
 * Façade pour la manipulation de chaînes de caractères
 * 
 * @method static string title(string $string)
 * @method static string slug(string $string)
 * @method static string studly(string $string)
 * @method static string kebab(string $string)
 * @method static string snake(string $string)
 * @method static string camel(string $string)
 * @method static string escape(string $string)
 * @method static string unescape(string $string)
 * @method static bool startsWith(string $string, string|array $needles)
 * @method static bool endsWith(string $string, string|array $needles)
 * @method static bool contains(string $haystack, string|array $needles)
 * @method static string replace(string $string, string $needle, string $replacement)
 * @method static string remove(string $string, string $needle)
 * @method static string removeAll(string $string, array $needles)
 * @method static string removeAllSpaces(string $string)
 * @method static string removeAllSpacesAndTrim(string $string)
 * @method static string lower(string $string)
 * @method static string upper(string $string)
 * @method static string capitalize(string $string)
 * @method static string reverse(string $string)
 * @method static int length(string $string)
 * @method static string random(int $length = 16)
 * @method static string randomNumber(int $length = 16)
 * @method static string limit(string $string, int $limit = 100, string $end = '...')
 */
class Str
{
   /**
    * Gère les appels statiques et les redirige vers la classe utilitaire
    *
    * @param string $method
    * @param array $arguments
    * @return mixed
    */
   public static function __callStatic(string $method, array $arguments)
   {
      return StrUtils::$method(...$arguments);
   }

   /**
    * Convertit une chaîne en titre
    */
   public static function title(string $string): string
   {
      return StrUtils::title($string);
   }

   /**
    * Convertit une chaîne en slug
    */
   public static function slug(string $string): string
   {
      return StrUtils::slug($string);
   }

   /**
    * Convertit une chaîne en StudlyCase
    */
   public static function studly(string $string): string
   {
      return StrUtils::studly($string);
   }

   /**
    * Convertit une chaîne en kebab-case
    */
   public static function kebab(string $string): string
   {
      return StrUtils::kebab($string);
   }

   /**
    * Convertit une chaîne en snake_case
    */
   public static function snake(string $string): string
   {
      return StrUtils::snake($string);
   }

   /**
    * Convertit une chaîne en camelCase
    */
   public static function camel(string $string): string
   {
      return StrUtils::camel($string);
   }

   /**
    * Échappe une chaîne pour l'affichage HTML
    */
   public static function escape(string $string): string
   {
      return StrUtils::escape($string);
   }

   /**
    * Décode les entités HTML dans une chaîne
    */
   public static function unescape(string $string): string
   {
      return StrUtils::unescape($string);
   }

   /**
    * Vérifie si une chaîne commence par une sous-chaîne
    */
   public static function startsWith(string $string, $needles): bool
   {
      return StrUtils::startsWith($string, $needles);
   }

   /**
    * Vérifie si une chaîne se termine par une sous-chaîne
    */
   public static function endsWith(string $string, $needles): bool
   {
      return StrUtils::endsWith($string, $needles);
   }

   /**
    * Vérifie si une chaîne contient une sous-chaîne
    */
   public static function contains(string $haystack, $needles): bool
   {
      return StrUtils::contains($haystack, $needles);
   }

   /**
    * Tronque une chaîne à la longueur spécifiée
    */
   public static function limit(string $value, int $limit = 100, string $end = '...'): string
   {
      return StrUtils::limit($value, $limit, $end);
   }

   /**
    * Génère un identifiant unique
    */
   public static function random(int $length = 16): string
   {
      return StrUtils::random($length);
   }
}
