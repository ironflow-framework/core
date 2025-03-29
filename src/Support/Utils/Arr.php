<?php

namespace IronFlow\Support\Utils;

/**
 * Classe utilitaire pour la manipulation de tableaux
 */
class Arr
{
   /**
    * Convertit une valeur en tableau
    *
    * @param mixed $value
    * @return array
    */
   public static function wrap($value): array
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
   public static function get(array $array, $key, $default = null)
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

   /**
    * Vérifie si une clé existe dans un tableau en utilisant la notation "dot"
    *
    * @param array $array
    * @param string|int $key
    * @return bool
    */
   public static function has(array $array, $key): bool
   {
      if (array_key_exists($key, $array)) {
         return true;
      }

      if (!str_contains($key, '.')) {
         return false;
      }

      foreach (explode('.', $key) as $segment) {
         if (!is_array($array) || !array_key_exists($segment, $array)) {
            return false;
         }

         $array = $array[$segment];
      }

      return true;
   }

   /**
    * Définit une valeur dans un tableau en utilisant la notation "dot"
    *
    * @param array $array
    * @param string|int $key
    * @param mixed $value
    * @return array
    */
   public static function set(array &$array, $key, $value): array
   {
      if (is_null($key)) {
         return $array = $value;
      }

      $keys = explode('.', $key);

      foreach ($keys as $i => $key) {
         if (count($keys) === 1) {
            break;
         }

         unset($keys[$i]);

         // Si la clé n'existe pas à ce niveau, on crée un tableau vide
         if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
         }

         $array = &$array[$key];
      }

      $array[array_shift($keys)] = $value;

      return $array;
   }
}
