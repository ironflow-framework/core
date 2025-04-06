<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Storage as StorageUtils;

/**
 * Façade pour le système de stockage
 * 
 * @method static StorageUtils disk(string $disk)
 * @method static bool exists(string $path)
 * @method static string|false get(string $path)
 * @method static bool put(string $path, string $contents)
 * @method static bool delete(string $path)
 * @method static string url(string $path)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = true)
 * @method static array files(string $directory)
 * @method static array directories(string $directory)
 */
class Storage extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return StorageUtils::class;
   }
   
}
