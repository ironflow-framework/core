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
}

// Création du répertoire de logs si nécessaire
if (!is_dir(BASE_PATH . '/storage/logs')) {
   mkdir(BASE_PATH . '/storage/logs', 0755, true);
}

// Démarrage de l'application
$app = require BASE_PATH . '/bootstrap/app.php';
$response = $app->run();

// Envoi de la réponse au client
$response->send();

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
