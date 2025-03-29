<?php

namespace IronFlow\Support\Utils;

/**
 * Classe utilitaire pour la gestion du stockage de fichiers
 */
class Storage
{
   /**
    * Configuration des disques de stockage
    */
   protected static array $disks = [];

   /**
    * Disque par défaut
    */
   protected static string $defaultDisk = 'local';

   /**
    * Chemin racine du disque courant
    */
   protected static string $rootPath = '';

   /**
    * Initialise la configuration des disques
    */
   public static function initialize(): void
   {
      static::$disks = config('filesystems.disks', []);
      static::$defaultDisk = config('filesystems.default', 'local');
   }

   /**
    * Sélectionne un disque de stockage à utiliser
    */
   public static function disk(string $disk): self
   {
      if (!isset(static::$disks[$disk])) {
         throw new \InvalidArgumentException("Le disque '{$disk}' n'est pas configuré.");
      }

      $instance = new self();
      $instance::$rootPath = static::$disks[$disk]['root'] ?? '';

      return $instance;
   }

   /**
    * Vérifie si un fichier existe
    */
   public static function exists(string $path): bool
   {
      return file_exists(static::getFullPath($path));
   }

   /**
    * Récupère le contenu d'un fichier
    */
   public static function get(string $path)
   {
      return file_get_contents(static::getFullPath($path));
   }

   /**
    * Écrit le contenu dans un fichier
    */
   public static function put(string $path, string $contents): bool
   {
      $directory = dirname(static::getFullPath($path));

      if (!is_dir($directory)) {
         mkdir($directory, 0755, true);
      }

      return file_put_contents(static::getFullPath($path), $contents) !== false;
   }

   /**
    * Supprime un fichier
    */
   public static function delete(string $path): bool
   {
      if (static::exists($path)) {
         return unlink(static::getFullPath($path));
      }

      return false;
   }

   /**
    * Récupère l'URL publique d'un fichier
    */
   public static function url(string $path): string
   {
      // À implémenter selon votre configuration
      return '/storage/' . $path;
   }

   /**
    * Récupère le chemin complet d'un fichier
    */
   protected static function getFullPath(string $path): string
   {
      return rtrim(static::$rootPath, '/') . '/' . ltrim($path, '/');
   }

   /**
    * Crée un répertoire
    */
   public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = true): bool
   {
      if (!is_dir(static::getFullPath($path))) {
         return mkdir(static::getFullPath($path), $mode, $recursive);
      }

      return true;
   }

   /**
    * Liste les fichiers d'un répertoire
    */
   public static function files(string $directory): array
   {
      $files = [];
      $fullPath = static::getFullPath($directory);

      if (is_dir($fullPath)) {
         $items = scandir($fullPath);

         foreach ($items as $item) {
            if ($item != '.' && $item != '..' && is_file($fullPath . '/' . $item)) {
               $files[] = $item;
            }
         }
      }

      return $files;
   }

   /**
    * Liste les répertoires d'un répertoire
    */
   public static function directories(string $directory): array
   {
      $directories = [];
      $fullPath = static::getFullPath($directory);

      if (is_dir($fullPath)) {
         $items = scandir($fullPath);

         foreach ($items as $item) {
            if ($item != '.' && $item != '..' && is_dir($fullPath . '/' . $item)) {
               $directories[] = $item;
            }
         }
      }

      return $directories;
   }
}
