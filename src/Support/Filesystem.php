<?php

namespace IronFlow\Support;

class Filesystem
{
   public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = true, bool $force = false): bool
   {
      if (file_exists($path) && !$force) {
         return false;
      }

      return mkdir($path, $mode, $recursive);
   }

   public static function put(string $path, string $content): bool
   {
      return file_put_contents($path, $content) !== false;
   }

   public static function get(string $path): string
   {
      return file_get_contents($path);
   }

   public static function exists(string $path): bool
   {
      return file_exists($path);
   }

   public static function delete(string $path): bool
   {
      return unlink($path);
   }

   public static function copy(string $source, string $destination): bool
   {
      return copy($source, $destination);
   }

   public static function move(string $source, string $destination): bool
   {
      return rename($source, $destination);
   }

   public static function size(string $path): int
   {
      return filesize($path);
   }

   public static function isDirectory(string $path): bool
   {
      return is_dir($path);
   }

   public static function isFile(string $path): bool
   {
      return is_file($path);
   }

   public static function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
   {
      if (!self::isDirectory($path)) {
         self::makeDirectory($path, $mode, $recursive);
      }
   }

   public static function isReadable(string $path): bool
   {
      return is_readable($path);
   }

   public static function isWritable(string $path): bool
   {
      return is_writable($path);
   }

   public static function copyDirectory(string $source, string $destination): bool
   {
      if (!is_dir($source)) {
         return false;
      }

      if (!is_dir($destination)) {
         mkdir($destination, 0755, true);
      }

      $files = new \RecursiveIteratorIterator(
         new \RecursiveDirectoryIterator($source),
         \RecursiveIteratorIterator::SELF_FIRST
      );

      foreach ($files as $file) {
         $relativePath = substr($file->getPathname(), strlen($source) + 1);
         $targetPath = $destination . '/' . $relativePath;

         if ($file->isDir()) {
            mkdir($targetPath, 0755, true);
         } else {
            copy($file->getPathname(), $targetPath);
         }
      }

      return true;
   }
}
