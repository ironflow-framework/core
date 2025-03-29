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
class Storage
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
      return StorageUtils::$method(...$arguments);
   }

   /**
    * Initialise le stockage
    */
   public static function initialize(): void
   {
      StorageUtils::initialize();
   }

   /**
    * Sélectionne un disque de stockage
    *
    * @param string $disk
    * @return StorageUtils
    */
   public static function disk(string $disk)
   {
      return StorageUtils::disk($disk);
   }

   /**
    * Vérifie si un fichier existe
    *
    * @param string $path
    * @return bool
    */
   public static function exists(string $path): bool
   {
      return StorageUtils::exists($path);
   }

   /**
    * Récupère le contenu d'un fichier
    *
    * @param string $path
    * @return string|false
    */
   public static function get(string $path)
   {
      return StorageUtils::get($path);
   }

   /**
    * Écrit le contenu dans un fichier
    *
    * @param string $path
    * @param string $contents
    * @return bool
    */
   public static function put(string $path, string $contents): bool
   {
      return StorageUtils::put($path, $contents);
   }

   /**
    * Supprime un fichier
    *
    * @param string $path
    * @return bool
    */
   public static function delete(string $path): bool
   {
      return StorageUtils::delete($path);
   }

   /**
    * Récupère l'URL publique d'un fichier
    *
    * @param string $path
    * @return string
    */
   public static function url(string $path): string
   {
      return StorageUtils::url($path);
   }
}
