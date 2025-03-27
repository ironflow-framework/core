<?php

use IronFlow\Database\Iron\Collection;

if (!function_exists('view_path')) {
   /**
    * Obtient le chemin vers le dossier des vues.
    *
    * @param string $path
    * @return string
    */
   function view_path(string $path = ''): string
   {
      return resource_path('views' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('config')) {
   /**
    * Obtient une valeur de configuration.
    *
    * @param string|null $key
    * @param mixed $default
    * @return mixed
    */
   function config(?string $key = null, mixed $default = null): mixed
   {
      if (is_null($key)) {
         return IronFlow\Support\Config::all();
      }

      return IronFlow\Support\Config::get($key, $default);
   }
}

if (!function_exists('env')) {
   /**
    * Obtient une variable d'environnement.
    *
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   function env(string $key, mixed $default = null): mixed
   {
      $value = getenv($key) || $_ENV[$key];

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

if (!function_exists('config_path')) {
   /**
    * Obtient le chemin vers le dossier de configuration.
    *
    * @param string $path
    * @return string
    */
   function config_path(string $path = ''): string
   {
      return app_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('app_path')) {
   /**
    * Obtient le chemin vers le dossier de l'application.
    *
    * @param string $path
    * @return string
    */
   function app_path(string $path = ''): string
   {
      return base_path('app') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
   }
}

if (!function_exists('base_path')) {
   /**
    * Obtient le chemin vers le dossier racine du projet.
    *
    * @param string $path
    * @return string
    */
   function base_path(string $path = ''): string
   {
      return dirname(__DIR__) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
   }
}

if (!function_exists('public_path')) {
   /**
    * Obtient le chemin vers le dossier public.
    *
    * @param string $path
    * @return string
    */
   function public_path(string $path = ''): string
   {
      return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('storage_path')) {
   /**
    * Obtient le chemin vers le dossier storage.
    *
    * @param string $path
    * @return string
    */
   function storage_path(string $path = ''): string
   {
      return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('resource_path')) {
   /**
    * Obtient le chemin vers le dossier resources.
    *
    * @param string $path
    * @return string
    */
   function resource_path(string $path = ''): string
   {
      return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('database_path')) {
   /**
    * Obtient le chemin vers le dossier database.
    *
    * @param string $path
    * @return string
    */
   function database_path(string $path = ''): string
   {
      return base_path('database' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
   }
}

if (!function_exists('collect')) {
   /**
    * Crée une nouvelle instance de collection à partir des éléments donnés
    * 
    * @param mixed $items
    * @return Collection
    */
   function collect($items = []): Collection
   {
      return new Collection($items);
   }
}

if (! function_exists('class_basename')) {
   /**
    * Get the class "basename" of the given object / class.
    *
    * @param  string|object  $class
    * @return string
    */
   function class_basename($class)
   {
      $class = is_object($class) ? get_class($class) : $class;

      return basename(str_replace('\\', '/', $class));
   }
}