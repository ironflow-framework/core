<?php

declare(strict_types=1);

/**
 * Bootstrap IronFlow
 * 
 * Ce fichier doit être inclus avant tout autre code IronFlow
 * pour s'assurer que l'environnement est correctement initialisé.
 */

// Vérification de la version PHP
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    throw new \RuntimeException('IronFlow requires PHP 8.0 or higher. Current version: ' . PHP_VERSION);
}

// Définir les constantes de base si pas déjà fait
if (!defined('IRONFLOW_START')) {
    define('IRONFLOW_START', microtime(true));
}

if (!defined('IRONFLOW_VERSION')) {
    define('IRONFLOW_VERSION', '1.0.0');
}

// Charger l'autoloader Composer
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
    __DIR__ . '/../../../../../vendor/autoload.php',
];

$autoloadPath = null;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if (!$autoloadPath) {
    throw new \RuntimeException(
        'Composer autoloader not found. Please run "composer install" in your project directory.'
    );
}

require_once $autoloadPath;

// Charger le système d'environnement
require_once __DIR__ . '/environment.php';

// Définir le répertoire racine du projet
if (!defined('IRONFLOW_BASE_PATH')) {
    $basePath = base_path();
    define('IRONFLOW_BASE_PATH', $basePath);
}

// Initialiser l'environnement
$envLoader = EnvironmentLoader::getInstance();

// Charger l'environnement si pas encore fait
if (!$envLoader->isLoaded()) {
    $envLoader->loadWithEnvironment(IRONFLOW_BASE_PATH);
}

// Configurer PHP selon l'environnement
$isProduction = env('APP_ENV') === 'production';
$debugMode = env('APP_DEBUG', !$isProduction);

// Configuration des erreurs
if ($debugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Configuration des logs
ini_set('log_errors', '1');
$logPath = storage_path('logs/php-errors.log');
if (is_dir(dirname($logPath))) {
    ini_set('error_log', $logPath);
}

// Configuration de la timezone
$timezone = env('APP_TIMEZONE', 'UTC');
if (!date_default_timezone_set($timezone)) {
    date_default_timezone_set('UTC');
    if ($debugMode) {
        trigger_error("Invalid timezone '{$timezone}', falling back to UTC", E_USER_WARNING);
    }
}

// Configuration de la locale
$locale = env('APP_LOCALE', 'en_US.UTF-8');
if (function_exists('setlocale')) {
    setlocale(LC_ALL, $locale);
}

// Configuration de l'encodage
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (function_exists('mb_http_output')) {
    mb_http_output('UTF-8');
}

// Configuration de la session si on est dans un contexte web
if (!defined('IRONFLOW_CLI') && session_status() === PHP_SESSION_NONE) {
    $sessionConfig = [
        'cookie_lifetime' => (int) env('SESSION_LIFETIME', 120) * 60,
        'cookie_path' => '/',
        'cookie_domain' => env('SESSION_DOMAIN', ''),
        'cookie_secure' => env('SESSION_SECURE_COOKIE', false),
        'cookie_httponly' => true,
        'cookie_samesite' => env('SESSION_SAME_SITE', 'lax'),
        'use_strict_mode' => true,
        'use_cookies' => true,
        'use_only_cookies' => true,
        'name' => env('SESSION_COOKIE', 'ironflow_session'),
    ];

    foreach ($sessionConfig as $key => $value) {
        if ($value !== null && $value !== '') {
            ini_set('session.' . $key, (string) $value);
        }
    }
}

// Gestionnaire d'erreurs personnalisé pour le mode développement
if ($debugMode && !defined('IRONFLOW_CLI')) {
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        $errorType = $errorTypes[$severity] ?? 'Unknown Error';

        // Logger l'erreur
        error_log("[{$errorType}] {$message} in {$file} on line {$line}");

        // En mode debug, afficher une page d'erreur formatée pour le web
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo formatDebugError($errorType, $message, $file, $line);

        if ($severity === E_ERROR || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
            exit(1);
        }

        return true;
    });
}

// Gestionnaire d'exceptions non capturées
set_exception_handler(function (Throwable $exception) use ($debugMode) {
    // Logger l'exception
    error_log('Uncaught Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());

    if (defined('IRONFLOW_CLI')) {
        // Mode CLI - affichage simple
        fwrite(STDERR, "Uncaught Exception: " . $exception->getMessage() . "\n");
        if ($debugMode) {
            fwrite(STDERR, $exception->getTraceAsString() . "\n");
        }
    } else {
        // Mode Web
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        if ($debugMode) {
            echo formatDebugException($exception);
        } else {
            echo formatProductionError();
        }
    }

    exit(1);
});

// Gestionnaire d'arrêt fatal
register_shutdown_function(function () use ($debugMode) {
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");

        if (!defined('IRONFLOW_CLI') && !headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');

            if ($debugMode) {
                echo formatDebugError('Fatal Error', $error['message'], $error['file'], $error['line']);
            } else {
                echo formatProductionError();
            }
        }
    }
});

// Validation de la configuration critique
try {
    $requiredEnvVars = ['APP_NAME', 'APP_ENV'];
    $envLoader->validateRequired($requiredEnvVars);
} catch (\RuntimeException $e) {
    if (defined('IRONFLOW_CLI')) {
        fwrite(STDERR, "Configuration Error: " . $e->getMessage() . "\n");
    } else {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo "Configuration Error: " . htmlspecialchars($e->getMessage());
    }
    exit(1);
}

// Optimisations pour la production
if ($isProduction) {
    // Désactiver les fonctions de debug potentiellement dangereuses
    $dangerousFunctions = ['exec', 'system', 'shell_exec', 'passthru'];
    foreach ($dangerousFunctions as $func) {
        if (function_exists($func)) {
            // Log pour audit de sécurité
            error_log("Security Notice: Dangerous function '{$func}' is available in production");
        }
    }

    // Configuration de sécurité renforcée
    ini_set('expose_php', '0');
    ini_set('allow_url_fopen', '0');
    ini_set('allow_url_include', '0');
}

// Marquer le bootstrap comme terminé
if (!defined('IRONFLOW_BOOTSTRAPPED')) {
    define('IRONFLOW_BOOTSTRAPPED', true);
}

// Calculer le temps de bootstrap
if (defined('IRONFLOW_START')) {
    $bootstrapTime = microtime(true) - IRONFLOW_START;
    if ($debugMode && $bootstrapTime > 0.1) { // Plus de 100ms
        error_log("IronFlow Bootstrap: Slow bootstrap detected ({$bootstrapTime}s)");
    }
}

/**
 * Formate une erreur pour l'affichage en mode debug
 */
function formatDebugError(string $type, string $message, string $file, int $line): string
{
    $relativePath = str_replace(IRONFLOW_BASE_PATH . DIRECTORY_SEPARATOR, '', $file);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$type} - IronFlow</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .error-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto; }
        .error-header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .error-title { margin: 0; font-size: 24px; font-weight: 600; }
        .error-subtitle { margin: 5px 0 0 0; opacity: 0.9; }
        .error-body { padding: 20px; }
        .error-message { background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 0 0 20px 0; font-family: monospace; font-size: 14px; }
        .error-location { color: #6c757d; margin-bottom: 20px; }
        .file-path { color: #007bff; font-weight: 600; }
        .line-number { color: #28a745; font-weight: 600; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <h1 class="error-title">{$type}</h1>
            <p class="error-subtitle">IronFlow Framework - Development Mode</p>
        </div>
        <div class="error-body">
            <div class="error-message">{$message}</div>
            <div class="error-location">
                in <span class="file-path">{$relativePath}</span> on line <span class="line-number">{$line}</span>
            </div>
        </div>
        <div class="footer">
            <p>IronFlow v" . IRONFLOW_VERSION . " | PHP " . PHP_VERSION . "</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Formate une exception pour l'affichage en mode debug
 */
function formatDebugException(Throwable $exception): string
{
    $type = get_class($exception);
    $message = $exception->getMessage();
    $file = str_replace(IRONFLOW_BASE_PATH . DIRECTORY_SEPARATOR, '', $exception->getFile());
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uncaught Exception - IronFlow</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .exception-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 1200px; margin: 0 auto; }
        .exception-header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .exception-title { margin: 0; font-size: 24px; font-weight: 600; }
        .exception-subtitle { margin: 5px 0 0 0; opacity: 0.9; }
        .exception-body { padding: 20px; }
        .exception-message { background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 0 0 20px 0; font-family: monospace; font-size: 14px; }
        .exception-location { color: #6c757d; margin-bottom: 20px; }
        .exception-trace { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; font-family: monospace; font-size: 12px; white-space: pre-wrap; overflow-x: auto; }
        .file-path { color: #007bff; font-weight: 600; }
        .line-number { color: #28a745; font-weight: 600; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="exception-container">
        <div class="exception-header">
            <h1 class="exception-title">Uncaught Exception</h1>
            <p class="exception-subtitle">{$type}</p>
        </div>
        <div class="exception-body">
            <div class="exception-message">{$message}</div>
            <div class="exception-location">
                in <span class="file-path">{$file}</span> on line <span class="line-number">{$line}</span>
            </div>
            <h3>Stack Trace:</h3>
            <div class="exception-trace">{$trace}</div>
        </div>
        <div class="footer">
            <p>IronFlow v" . IRONFLOW_VERSION . " | PHP " . PHP_VERSION . "</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Formate une page d'erreur pour la production
 */
function formatProductionError(): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; text-align: center; }
        .error-container { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 50px auto; padding: 40px; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
        .error-title { color: #333; font-size: 32px; margin-bottom: 10px; }
        .error-message { color: #666; font-size: 18px; margin-bottom: 30px; }
        .error-code { color: #999; font-size: 14px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-message">Something went wrong on our end. Please try again later.</p>
        <p class="error-code">Error Code: 500</p>
    </div>
</body>
</html>
HTML;
}
