<?php

declare(strict_types=1);

namespace IronFlow\Support;

class Helpers
{
    public static function classUsesRecursive($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('trait_uses_recursive')) {
    function trait_uses_recursive($trait): array
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('env')) {
   function env(string $key, mixed $default = null): mixed
   {
      $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

      if ($value === false) {
         return $default;
      }

      switch (strtolower($value)) {
         case 'true':
         case '(true)':
            return true;
         case 'false':
         case '(false)':
            return false;
         case 'null':
         case '(null)':
            return null;
         case 'empty':
         case '(empty)':
            return '';
      }

      return $value;
   }
}

if (!function_exists('base_path')) {
   function base_path(string $path = ''): string
   {
      return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : '');
   }
}

if (!function_exists('config_path')) {
   function config_path(string $path = ''): string
   {
      return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
   }
}

if (!function_exists('resource_path')) {
   function resource_path(string $path = ''): string
   {
      return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
   }
}

if (!function_exists('storage_path')) {
   function storage_path(string $path = ''): string
   {
      return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
   }
}

if (!function_exists('public_path')) {
   function public_path(string $path = ''): string
   {
      return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
   }
}

if (!function_exists('database_path')) {
   function database_path(string $path = ''): string
   {
      return base_path('database' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
   }
}
