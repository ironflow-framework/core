<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Arr as ArrUtils;

/**
 * Façade pour la manipulation de tableaux
 * 
 * @method static array wrap($value)
 * @method static bool isAssoc(array $array)
 * @method static array flatten(array $array, string $prepend = '', string $delimiter = '.')
 * @method static mixed get(array $array, string|int|null $key, mixed $default = null)
 * @method static bool has(array $array, string|int $key)
 * @method static array set(array &$array, string|int $key, mixed $value)
 */
class Arr
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
      return ArrUtils::$method(...$arguments);
   }

   /**
    * Convertit une valeur en tableau
    *
    * @param mixed $value
    * @return array
    */
   public static function wrap($value): array
   {
      return ArrUtils::wrap($value);
   }

   /**
    * Détermine si un tableau est associatif
    *
    * @param array $array
    * @return bool
    */
   public static function isAssoc(array $array): bool
   {
      return ArrUtils::isAssoc($array);
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
      return ArrUtils::flatten($array, $prepend, $delimiter);
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
      return ArrUtils::get($array, $key, $default);
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
      return ArrUtils::has($array, $key);
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
      return ArrUtils::set($array, $key, $value);
   }
}
