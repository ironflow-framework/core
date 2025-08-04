<?php

declare(strict_types=1);

/**
 * Fonctions helper globales
 */

if (!function_exists("base_path")) {
    /**
     * Retourne le chemin de base de l'application
     */
    function base_path(string $path = ''): string
    {
        return \IronFlow\Core\Application::getInstance()->getBasePath() . $path;
    }
}

if (!function_exists("database_path")) {
    /**
     * Retourne le chemin de dossier database global ou celui d'un module
     */
    function database_path(string $path = ""): string
    {
        $baseRootDb = \IronFlow\Core\Application::getInstance()->getBasePath() . '/database';
        $modulesPath = \IronFlow\Core\Application::getInstance()->getBasePath() . '/Modules';

        if ($path === '') {
            return $baseRootDb;
        }

        // Nettoyage du chemin
        $cleanedPath = trim($path, '/');
        $segments = explode('/', $cleanedPath);
        $first = array_shift($segments);

        if (is_module($first)) {
            $moduleDbPath = "$modulesPath/$first/database";

            if (is_dir($moduleDbPath)) {
                unset($segments[0]);
                $subPath = implode('/', $segments);
                return rtrim($moduleDbPath . '/' . $subPath, '/');
            }
        }

        return rtrim($baseRootDb . '/' . $cleanedPath, '/');
    }
}


if (!function_exists('is_module')) {
    /**
     * Vérifie si le dossier est un module
     */
    function is_module(string $name = ''): bool
    {
        return is_dir(base_path('/modules/' . ucfirst($name)));
    }
}

if (!function_exists('storage_path')) {
    /**
     * 
     */
    function storage_path(string $path = ''): string
    {
        return \IronFlow\Core\Application::getInstance()->getBasePath() . '/storage';
    }
}

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

declare(strict_types=1);

if (!function_exists('collect')) {
    /**
     * Crée une nouvelle collection
     */
    function collect(array $items = []): IronFlow\Core\Database\Collection
    {
        return new IronFlow\Core\Database\Collection($items);
    }
}

if (!function_exists('db')) {
    /**
     * Raccourci vers l'instance Database
     */
    function db(): \IronFlow\Core\Database\Database
    {
        return \IronFlow\Core\Database\Database::getInstance();
    }
}

if (!function_exists('config')) {
    /**
     * Récupère une valeur de configuration
     */
    function config(string $key, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $configFile = __DIR__ . '/../../../../config/app.php';
            $config = file_exists($configFile) ? require $configFile : [];
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('env')) {
    /**
     * Récupère une variable d'environnement
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Conversion des valeurs booléennes
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

        // Valeurs entre guillemets
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}

if (!function_exists('cache')) {
    /**
     * Raccourci vers le cache
     */
    function cache(): \IronFlow\Core\Cache\CacheInterface
    {
        return \IronFlow\Core\Cache\MemoryCache::getInstance();
    }
}
