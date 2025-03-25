<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

use IronFlow\Application\Application;
use IronFlow\Core\ErrorHandler;
use IronFlow\Support\Config;
use IronFlow\View\TwigView;
use IronFlow\Http\Response;

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
   if (!is_dir($directory)) {
      if (!mkdir($directory, 0755, true)) {
         throw new \RuntimeException("Impossible de créer le répertoire: {$directory}");
      }
   }
}

// Initialisation de la configuration
Config::load(config_path());

// Initialisation du moteur de vue
$view = new TwigView(view_path());
Response::setView($view);

// Enregistrement du gestionnaire d'erreurs
ErrorHandler::register();

$app = new Application();

return $app->withBasePath(dirname(__DIR__))
   ->configure(
      require dirname(__DIR__) . '/config/app.php',
      require dirname(__DIR__) . '/config/services.php'
   )
   ->withRoutes(
      dirname(__DIR__) . '/routes/web.php',
      dirname(__DIR__) . '/routes/api.php'
   )
   ->build();
