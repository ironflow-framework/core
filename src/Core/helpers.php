<?php

declare(strict_types=1);

/**
 * Fonctions helper globales pour l'application IronFlow.
 * Fournit des raccourcis vers les chemins, la configuration, la base de données, etc.
 */

// Chemin de base de l'application
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 6);
        return $path ? $root . '/' . ltrim($path, '/') : $root;
    }
}

// Chemin vers le dossier database (global ou module)
if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        $defaultPath = base_path('database');
        $modulesPath = base_path('Modules');

        if ($path === '') return $defaultPath;

        $segments = explode('/', trim($path, '/'));
        $first = ucfirst(array_shift($segments));

        if (is_module($first)) {
            $moduleDbPath = "$modulesPath/$first/database";
            if (is_dir($moduleDbPath)) {
                return rtrim("$moduleDbPath/" . implode('/', $segments), '/');
            }
        }

        return rtrim("$defaultPath/" . implode('/', array_merge([$first], $segments)), '/');
    }
}

// Vérifie si un module existe
if (!function_exists('is_module')) {
    function is_module(string $name): bool
    {
        return is_dir(base_path('Modules/' . ucfirst($name)));
    }
}

// Chemin vers le dossier de stockage
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

// Traduction (i18n)
if (!function_exists('trans')) {
    function trans(string $key, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        $translator = IronFlow\Core\Translation\TranslationHelper::getInstance();
        return $translator ? $translator->trans($key, $parameters, $domain, $locale) : $key;
    }
}

if (!function_exists('trans_choice')) {
    function trans_choice(string $key, int $count, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        $translator = IronFlow\Core\Translation\TranslationHelper::getInstance();
        return $translator ? $translator->transChoice($key, $count, $parameters, $domain, $locale) : $key;
    }
}

if (!function_exists('__')) {
    function __(string $key, array $parameters = []): string
    {
        return trans($key, $parameters);
    }
}

if (!function_exists('_n')) {
    function _n(string $key, int $count, array $parameters = []): string
    {
        return trans_choice($key, $count, $parameters);
    }
}

// Collection d'objets
if (!function_exists('collect')) {
    function collect(array $items = []): IronFlow\Core\Database\Collection
    {
        return new IronFlow\Core\Database\Collection($items);
    }
}

// Accès à l'instance de la base de données
if (!function_exists('db')) {
    function db(): IronFlow\Core\Database\Database
    {
        return IronFlow\Core\Database\Database::getInstance();
    }
}

// Chargement de configuration
if (!function_exists('config')) {
    function config(string $file, string $key, $default = null)
    {
        static $configCache = [];

        if (!isset($configCache[$file])) {
            $filePath = base_path("config/$file.php");
            $configCache[$file] = file_exists($filePath) ? require $filePath : [];
        }

        $value = $configCache[$file];
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

// Récupération des variables d'environnement avec vlucas/phpdotenv
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (class_exists(\Dotenv\Dotenv::class) && empty($_ENV)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(base_path());
            $dotenv->load();
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) return $default;

        $value = trim($value);
        $map = [
            'true' => true, '(true)' => true,
            'false' => false, '(false)' => false,
            'null' => null, '(null)' => null,
            'empty' => '', '(empty)' => ''
        ];

        $lower = strtolower($value);
        return array_key_exists($lower, $map) ? $map[$lower] : trim($value, '"');
    }
}

// Raccourci vers le gestionnaire de cache
if (!function_exists('cache')) {
    function cache(): IronFlow\Core\Cache\CacheInterface
    {
        return IronFlow\Core\Cache\MemoryCache::getInstance();
    }
}
