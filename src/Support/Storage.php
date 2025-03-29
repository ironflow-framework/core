<?php

namespace IronFlow\Support;

/**
 * Classe utilitaire pour la gestion du stockage de fichiers
 */
class Storage
{
   /**
    * Configuration des disques de stockage
    * 
    * @var array
    */
   protected static array $disks = [];

   /**
    * Disque par défaut
    * 
    * @var string
    */
   protected static string $defaultDisk = 'local';

   /**
    * Chemin racine du disque courant
    * 
    * @var string
    */
   protected static string $rootPath = '';

   /**
    * Initialise la configuration des disques
    * 
    * @return void
    */
   public static function initialize(): void
   {
      static::$disks = config('filesystems.disks', []);
      static::$defaultDisk = config('filesystems.default', 'local');
   }

   /**
    * Sélectionne un disque de stockage à utiliser
    * 
    * @param string $disk
    * @return self
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
    * 
    * @param string $path
    * @return bool
    */
   public static function exists(string $path): bool
   {
      return file_exists(static::getFullPath($path));
   }

   /**
    * Récupère le contenu d'un fichier
    * 
    * @param string $path
    * @return string|false
    */
   public static function get(string $path)
   {
      return file_get_contents(static::getFullPath($path));
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
      $directory = dirname(static::getFullPath($path));

      if (!is_dir($directory)) {
         mkdir($directory, 0755, true);
      }

      return file_put_contents(static::getFullPath($path), $contents) !== false;
   }

   /**
    * Supprime un fichier
    * 
    * @param string $path
    * @return bool
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
    * 
    * @param string $path
    * @return string
    */
   public static function url(string $path): string
   {
      // À implémenter selon votre configuration
      return '/storage/' . $path;
   }

   /**
    * Récupère le chemin complet d'un fichier
    * 
    * @param string $path
    * @return string
    */
   protected static function getFullPath(string $path): string
   {
      return rtrim(static::$rootPath, '/') . '/' . ltrim($path, '/');
   }
}
