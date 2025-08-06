<?php

declare(strict_types=1);

/**
 * Fonctions helper pour la gestion de l'environnement IronFlow
 * 
 * Ce fichier contient les fonctions utilitaires pour la gestion
 * des variables d'environnement et autres helpers globaux.
 */

if (!function_exists("load_booststrap")) {
    /**
     * Charge le bootstrap de l'application IronFlow
     *
     * @return void
     */
    function load_booststrap(): void
    {
        require_once __DIR__ . '/bootstrap.php';
    }
}

if (!function_exists('env')) {
    /**
     * Récupère une variable d'environnement avec valeur par défaut
     *
     * @param string $key Clé de la variable d'environnement
     * @param mixed $default Valeur par défaut si la variable n'existe pas
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Conversion des valeurs booléennes et nulles
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        // Gestion des valeurs entre guillemets
        if (strlen($value) > 1 && $value[0] === '"' && $value[-1] === '"') {
            return substr($value, 1, -1);
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Récupère une valeur de configuration
     *
     * @param string $key Clé de configuration (ex: 'app.name')
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $configCache = [];
        
        // Séparer le fichier de configuration et la clé
        $parts = explode('.', $key, 2);
        $file = $parts[0];
        $configKey = $parts[1] ?? null;
        
        // Charger la configuration si pas encore en cache
        if (!isset($configCache[$file])) {
            $configPath = base_path("config/{$file}.php");
            
            if (file_exists($configPath)) {
                $configCache[$file] = require $configPath;
            } else {
                $configCache[$file] = [];
            }
        }
        
        $config = $configCache[$file];
        
        // Si pas de clé spécifique, retourner toute la configuration
        if ($configKey === null) {
            return $config;
        }
        
        // Navigation dans la configuration avec notation dot
        $keys = explode('.', $configKey);
        $value = $config;
        
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Retourne le chemin de base de l'application
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function base_path(string $path = ''): string
    {
        static $basePath = null;
        
        if ($basePath === null) {
            // Détecter le chemin de base en remontant depuis vendor
            $currentDir = __DIR__;
            
            // Chercher le répertoire contenant composer.json
            while ($currentDir !== dirname($currentDir)) {
                if (file_exists($currentDir . '/composer.json')) {
                    $basePath = $currentDir;
                    break;
                }
                $currentDir = dirname($currentDir);
            }
            
            // Fallback
            if ($basePath === null) {
                $basePath = getcwd();
            }
        }
        
        return $basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('app_path')) {
    /**
     * Retourne le chemin du répertoire app
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('config_path')) {
    /**
     * Retourne le chemin du répertoire config
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Retourne le chemin du répertoire storage
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('public_path')) {
    /**
     * Retourne le chemin du répertoire public
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('database_path')) {
    /**
     * Retourne le chemin du répertoire database
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('resource_path')) {
    /**
     * Retourne le chemin du répertoire resources
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function resource_path(string $path = ''): string
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('view_path')) {
    /**
     * Retourne le chemin du répertoire views
     *
     * @param string $path Chemin relatif à ajouter
     * @return string
     */
    function view_path(string $path = ''): string
    {
        return resource_path('views' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('asset')) {
    /**
     * Génère une URL pour un asset
     *
     * @param string $path Chemin de l'asset
     * @return string
     */
    function asset(string $path): string
    {
        $baseUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Génère une URL pour l'application
     *
     * @param string $path Chemin relatif
     * @return string
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('abort')) {
    /**
     * Lance une exception HTTP
     *
     * @param int $code Code d'erreur HTTP
     * @param string $message Message d'erreur
     * @throws \RuntimeException
     */
    function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        
        if (empty($message)) {
            $messages = [
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                422 => 'Unprocessable Entity',
                500 => 'Internal Server Error',
                503 => 'Service Unavailable',
            ];
            $message = $messages[$code] ?? 'HTTP Error';
        }
        
        throw new \RuntimeException($message, $code);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - Affiche les variables et arrête l'exécution
     *
     * @param mixed ...$vars Variables à afficher
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Affiche une variable de manière formatée
     *
     * @param mixed $var Variable à afficher
     */
    function dump(mixed $var): void
    {
        var_dump($var);
    }
}

if (!function_exists('collect')) {
    /**
     * Crée une collection à partir d'un tableau
     *
     * @param array $items Éléments de la collection
     * @return array
     */
    function collect(array $items = []): array
    {
        // Implémentation basique - peut être améliorée avec une vraie classe Collection
        return $items;
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne valeur de formulaire
     *
     * @param string $key Clé de la valeur
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    /**
     * Récupère ou définit une valeur de session
     *
     * @param string|array|null $key Clé de la session ou array pour définir plusieurs valeurs
     * @param mixed $default Valeur par défaut ou valeur à définir
     * @return mixed
     */
    function session(string|array|null $key = null, mixed $default = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($key === null) {
            return $_SESSION;
        }
        
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return true;
        }
        
        if (func_num_args() === 1) {
            return $_SESSION[$key] ?? $default;
        }
        
        $_SESSION[$key] = $default;
        return true;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Génère un token CSRF
     *
     * @return string
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_token'];
    }
}

if (!function_exists('now')) {
    /**
     * Retourne la date/heure actuelle
     *
     * @param string|null $timezone Timezone optionnel
     * @return \DateTime
     */
    function now(?string $timezone = null): \DateTime
    {
        return new \DateTime('now', $timezone ? new \DateTimeZone($timezone) : null);
    }
}

if (!function_exists('str_random')) {
    /**
     * Génère une chaîne aléatoire
     *
     * @param int $length Longueur de la chaîne
     * @return string
     */
    function str_random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Convertit une chaîne en slug
     *
     * @param string $title Titre à convertir
     * @param string $separator Séparateur
     * @return string
     */
    function str_slug(string $title, string $separator = '-'): string
    {
        // Convertir en minuscules
        $title = strtolower($title);
        
        // Remplacer les caractères accentués
        $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        
        // Remplacer les caractères non alphanumériques par le séparateur
        $title = preg_replace('/[^a-z0-9]+/', $separator, $title);
        
        // Supprimer les séparateurs en début et fin
        $title = trim($title, $separator);
        
        return $title;
    }
}

// Raccourci vers le gestionnaire de cache
if (!function_exists('cache')) {
    function cache(): IronFlow\Core\Cache\CacheInterface
    {
        return IronFlow\Core\Cache\MemoryCache::getInstance();
    }
}
