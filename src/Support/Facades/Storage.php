<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Storage as StorageService;

/**
 * Façade pour la classe Storage
 *
 * @method static \IronFlow\Support\Storage disk(string $disk)
 * @method static bool exists(string $path)
 * @method static string|false get(string $path)
 * @method static bool put(string $path, string $contents)
 * @method static bool delete(string $path)
 * @method static string url(string $path)
 */
class Storage
{
   /**
    * Gère les appels de méthodes statiques et les redirige vers l'instance Storage
    *
    * @param string $method
    * @param array $parameters
    * @return mixed
    */
   public static function __callStatic(string $method, array $parameters)
   {
      return StorageService::$method(...$parameters);
   }
}
