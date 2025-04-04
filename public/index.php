<?php

declare(strict_types=1);

/**
 * Point d'entrée de l'application IronFlow
 * 
 * Ce fichier initialise l'environnement de l'application, charge les dépendances
 * et démarre l'application.
 * 
 * @author IronFlow Team
 * @version 1.0.0
 */

// Définition des constantes de démarrage pour mesurer les performances
define('IRONFLOW_START', microtime(true));
define('IRONFLOW_MEMORY_USAGE', memory_get_usage());

// Vérification de la version de PHP
const MINIMUM_PHP_VERSION = '8.1.0';
if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<')) {
   throw new RuntimeException(sprintf(
      'IronFlow nécessite PHP %s ou supérieur. Version actuelle : %s',
      MINIMUM_PHP_VERSION,
      PHP_VERSION
   ));
}

// Définition du chemin de base de l'application
define('BASE_PATH', dirname(__DIR__));

// Chargement de l'autoloader de Composer
require_once BASE_PATH . '/vendor/autoload.php';

// Chargement des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Configuration de l'environnement
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Configuration du mode développement
if ($_ENV['APP_ENV'] === 'development') {
   error_reporting(E_ALL);
   ini_set('display_errors', '1');
   ini_set('log_errors', '1');
   ini_set('error_log', BASE_PATH . '/storage/logs/php_errors.log');
   ini_set('display_startup_errors', '1');
   ini_set('track_errors', '1');
   ini_set('html_errors', '1');
   ini_set('docref_root', '');
   ini_set('docref_ext', '.html');

   // Configuration du logging personnalisé
   ini_set('error_prepend_string', '[' . date('Y-m-d H:i:s') . '] ');
   ini_set('error_append_string', "\n");

   // Test de logging
   error_log("=== Démarrage de l'application ===");
   error_log("PHP Version: " . PHP_VERSION);
   error_log("Memory Limit: " . ini_get('memory_limit'));
   error_log("Max Execution Time: " . ini_get('max_execution_time'));
   error_log("Error Reporting: " . error_reporting());
   error_log("Display Errors: " . ini_get('display_errors'));
   error_log("Log Errors: " . ini_get('log_errors'));
   error_log("Error Log: " . ini_get('error_log'));
   error_log("================================\n");
}

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Création du répertoire de logs si nécessaire
if (!is_dir(BASE_PATH . '/storage/logs')) {
   mkdir(BASE_PATH . '/storage/logs', 0755, true);
}

// Vérification des permissions du fichier de log
$logFile = BASE_PATH . '/storage/logs/php_errors.log';
if (!file_exists($logFile)) {
   touch($logFile);
}
chmod($logFile, 0666);

try {
   // Démarrage de l'application
   error_log("Chargement de l'application...");
   $app = require BASE_PATH . '/bootstrap/app.php';
   error_log("Application chargée, démarrage...");
   $app->run();
} catch (\Throwable $e) {
   error_log("ERREUR CRITIQUE: " . $e->getMessage());
   error_log("Fichier: " . $e->getFile() . ":" . $e->getLine());
   error_log("Trace: " . $e->getTraceAsString());

   // Gestion des erreurs non capturées
   if (isset($app)) {
      $response = $app->handleException($e);
      $response->send();
   } else {
      // Fallback en cas d'erreur avant l'initialisation de l'application
      http_response_code(500);
      if ($_ENV['APP_ENV'] === 'development') {
         echo sprintf(
            "Une erreur est survenue : %s\n%s\n%s",
            $e->getMessage(),
            $e->getFile() . ':' . $e->getLine(),
            $e->getTraceAsString()
         );
      } else {
         echo "Une erreur est survenue. Veuillez réessayer plus tard.";
      }
   }
   exit(1);
}

// Affichage des métriques de performance en mode debug
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
   $executionTime = number_format((microtime(true) - IRONFLOW_START) * 1000, 2);
   $memoryUsage = number_format((memory_get_usage() - IRONFLOW_MEMORY_USAGE) / 1024 / 1024, 2);

   printf(
      "\n<!-- Temps d'exécution: %s ms | Mémoire utilisée: %s MB -->",
      $executionTime,
      $memoryUsage
   );
}
