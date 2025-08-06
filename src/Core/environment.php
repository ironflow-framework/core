<?php

declare(strict_types=1);

/**
 * Chargeur d'environnement IronFlow
 * 
 * Ce fichier s'occupe du chargement des variables d'environnement
 * et de l'initialisation des helpers globaux.
 */

// Chargement des helpers avant tout
require_once __DIR__ . '/helpers.php';

use Dotenv\Dotenv;

/**
 * Classe de gestion de l'environnement
 */
class EnvironmentLoader
{
    private static ?self $instance = null;
    private bool $loaded = false;
    private array $loadedFiles = [];

    private function __construct()
    {
        // Singleton
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Charge l'environnement depuis les fichiers .env
     *
     * @param string $path Chemin vers le répertoire contenant les fichiers .env
     * @param string|array $names Nom(s) des fichiers .env à charger
     * @return self
     */
    public function load(string $path, string|array $names = '.env'): self
    {
        if ($this->loaded) {
            return $this;
        }

        try {
            // S'assurer que le chemin existe
            if (!is_dir($path)) {
                throw new \InvalidArgumentException("Environment path does not exist: {$path}");
            }

            $names = is_array($names) ? $names : [$names];
            $loadedAny = false;

            foreach ($names as $name) {
                $filePath = $path . DIRECTORY_SEPARATOR . $name;
                
                if (file_exists($filePath)) {
                    $dotenv = Dotenv::createImmutable($path, $name);
                    $dotenv->load();
                    
                    $this->loadedFiles[] = $filePath;
                    $loadedAny = true;
                    
                    // Log pour debug
                    if (function_exists('error_log')) {
                        error_log("IronFlow: Loaded environment file: {$filePath}");
                    }
                }
            }

            if (!$loadedAny && in_array('.env', $names)) {
                // Créer un fichier .env par défaut si aucun n'existe
                $this->createDefaultEnvFile($path);
            }

            $this->loaded = true;
            
        } catch (\Throwable $e) {
            // En cas d'erreur, logger mais ne pas arrêter l'application
            if (function_exists('error_log')) {
                error_log("IronFlow Environment Loader Error: " . $e->getMessage());
            }
            
            // Charger des valeurs par défaut minimales
            $this->loadDefaultEnvironment();
        }

        return $this;
    }

    /**
     * Charge l'environnement avec des fichiers spécifiques selon l'environnement
     *
     * @param string $path Chemin vers le répertoire des fichiers .env
     * @param string|null $environment Environnement spécifique (development, production, etc.)
     * @return self
     */
    public function loadWithEnvironment(string $path, ?string $environment = null): self
    {
        // Détecter l'environnement si non spécifié
        if ($environment === null) {
            $environment = $this->detectEnvironment();
        }

        // Liste des fichiers à charger par ordre de priorité
        $files = [
            '.env',                           // Base
            ".env.{$environment}",           // Spécifique à l'environnement
            '.env.local',                    // Local (jamais commité)
            ".env.{$environment}.local"      // Local + env spécifique
        ];

        return $this->load($path, $files);
    }

    /**
     * Détecte l'environnement d'exécution
     */
    private function detectEnvironment(): string
    {
        // Vérifier les variables d'environnement système d'abord
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');
        
        if ($env) {
            return $env;
        }

        // Détecter selon le contexte
        if (php_sapi_name() === 'cli') {
            return 'development';
        }

        // Détecter selon l'hostname ou IP
        $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        if (in_array($serverName, ['localhost', '127.0.0.1', '::1']) || 
            str_contains($serverName, '.local') ||
            str_contains($serverName, '.dev')) {
            return 'development';
        }

        return 'production';
    }

    /**
     * Crée un fichier .env par défaut
     */
    private function createDefaultEnvFile(string $path): void
    {
        $envPath = $path . DIRECTORY_SEPARATOR . '.env';
        
        $defaultContent = <<<ENV
# Configuration IronFlow
APP_NAME=IronFlow
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ironflow
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

# Logs
LOG_CHANNEL=single
LOG_LEVEL=debug

ENV;

        try {
            file_put_contents($envPath, $defaultContent);
            $this->loadedFiles[] = $envPath;
            
            if (function_exists('error_log')) {
                error_log("IronFlow: Created default .env file at: {$envPath}");
            }
        } catch (\Throwable $e) {
            if (function_exists('error_log')) {
                error_log("IronFlow: Could not create default .env file: " . $e->getMessage());
            }
        }
    }

    /**
     * Charge des variables d'environnement par défaut
     */
    private function loadDefaultEnvironment(): void
    {
        $defaults = [
            'APP_NAME' => 'IronFlow',
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost:8000',
            'APP_KEY' => '',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'ironflow',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
            'LOG_CHANNEL' => 'single',
            'LOG_LEVEL' => 'debug',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($_ENV[$key]) && !isset($_SERVER[$key]) && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Vérifie si l'environnement a été chargé
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Obtient la liste des fichiers chargés
     */
    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    /**
     * Recharge l'environnement
     */
    public function reload(): self
    {
        $this->loaded = false;
        $this->loadedFiles = [];
        return $this;
    }

    /**
     * Valide que les variables d'environnement requises sont présentes
     */
    public function validateRequired(array $required): void
    {
        $missing = [];
        
        foreach ($required as $key) {
            if (env($key) === null) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new \RuntimeException(
                'Missing required environment variables: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Obtient toutes les variables d'environnement chargées
     */
    public function all(): array
    {
        return $_ENV;
    }

    /**
     * Génère une clé d'application aléatoire
     */
    public function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}

// Auto-chargement de l'environnement si on détecte qu'on est dans un projet IronFlow
if (!defined('IRONFLOW_ENV_LOADED')) {
    $loader = EnvironmentLoader::getInstance();
    
    // Chercher le répertoire racine du projet
    $currentDir = __DIR__;
    $projectRoot = null;
    
    while ($currentDir !== dirname($currentDir)) {
        if (file_exists($currentDir . DIRECTORY_SEPARATOR . 'composer.json') && file_exists($currentDir . DIRECTORY_SEPARATOR . '.env.example') && file_exists($currentDir . DIRECTORY_SEPARATOR .'public/index.php')) {
            $projectRoot = $currentDir;
            break;
        }
        $currentDir = dirname($currentDir);
    }
    
    if ($projectRoot) {
        $loader->loadWithEnvironment($projectRoot);
        define('IRONFLOW_ENV_LOADED', true);
    }
}