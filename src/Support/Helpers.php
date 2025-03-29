<?php

declare(strict_types=1);

namespace IronFlow\Support;

use IronFlow\Http\Request;

/**
 * Classe utilitaire avec des helpers divers pour le framework
 */
class Helpers
{
   /**
    * Récupère tous les traits utilisés par une classe, y compris récursivement
    *
    * @param string|object $class
    * @return array
    */
   public static function classUsesRecursive($class): array
   {
      if (is_object($class)) {
         $class = get_class($class);
      }

      $results = [];

      foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
         $results += self::traitUsesRecursive($class);
      }

      return array_unique($results);
   }

   /**
    * Récupère tous les traits utilisés par un trait, y compris récursivement
    *
    * @param string $trait
    * @return array
    */
   public static function traitUsesRecursive(string $trait): array
   {
      $traits = class_uses($trait);

      foreach ($traits as $trait) {
         $traits += self::traitUsesRecursive($trait);
      }

      return $traits;
   }

   /**
    * Convertit une valeur en tableau
    *
    * @param mixed $value
    * @return array
    */
   public static function arrayWrap($value): array
   {
      if (is_null($value)) {
         return [];
      }

      return is_array($value) ? $value : [$value];
   }

   /**
    * Détermine si un tableau est associatif
    *
    * @param array $array
    * @return bool
    */
   public static function isAssoc(array $array): bool
   {
      $keys = array_keys($array);

      return array_keys($keys) !== $keys;
   }

   /**
    * Aplatit un tableau multi-dimensionnel en utilisant un délimiteur
    *
    * @param array $array
    * @param string $prepend
    * @param string $delimiter
    * @return array
    */
   public static function flatten(array $array, string $prepend = '', string $delimiter = '.'): array
   {
      $results = [];

      foreach ($array as $key => $value) {
         $newKey = $prepend . ($prepend ? $delimiter : '') . $key;

         if (is_array($value) && !empty($value)) {
            $results = array_merge($results, self::flatten($value, $newKey, $delimiter));
         } else {
            $results[$newKey] = $value;
         }
      }

      return $results;
   }

   /**
    * Obtient un élément d'un tableau en utilisant la notation "dot"
    *
    * @param array $array
    * @param string|int|null $key
    * @param mixed $default
    * @return mixed
    */
   public static function arrayGet(array $array, $key, $default = null)
   {
      if (is_null($key)) {
         return $array;
      }

      if (array_key_exists($key, $array)) {
         return $array[$key];
      }

      if (!str_contains($key, '.')) {
         return $array[$key] ?? $default;
      }

      foreach (explode('.', $key) as $segment) {
         if (is_array($array) && array_key_exists($segment, $array)) {
            $array = $array[$segment];
         } else {
            return $default;
         }
      }

      return $array;
   }
}
