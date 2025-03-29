<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

use IronFlow\Foundation\Application;
use IronFlow\Core\ErrorHandler;
use IronFlow\Support\Facades\Config;
use IronFlow\View\TwigView;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Filesystem;

// Création des répertoires nécessaires
$directories = [
   view_path(),
   storage_path('cache'),
   storage_path('logs'),
   storage_path('sessions'),
   public_path('assets'),
   resource_path('views/layouts'),
   resource_path('views/components'),
   resource_path('views/errors')
];

foreach ($directories as $directory) {
   if (!Filesystem::exists($directory)) {
      if (!mkdir($directory, 0755, true)) {
         throw new \RuntimeException("Impossible de créer le répertoire: {$directory}");
      }
   }
}

// Initialisation de la configuration
Config::load();

// Initialisation du moteur de vue
$view = new TwigView(view_path());
Response::setView($view);

// Enregistrement du gestionnaire d'erreurs
ErrorHandler::register();

// Création de l'application
$app = new Application(dirname(__DIR__));

// Configuration de l'application
$app->configure(require dirname(__DIR__) . '/config/app.php');

// Chargement des fournisseurs de services
$services = require dirname(__DIR__) . '/config/services.php';
foreach ($services as $service) {
   $app->register($service);
}

// Démarrage de l'application
$app->boot();

return $app;
