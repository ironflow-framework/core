<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

use IronFlow\Core\Application;

// Création de l'application
$app = new Application(dirname(__DIR__));

// Configuration de l'application
$app->configure(require dirname(__DIR__) . '/config/app.php');

// Démarrage de l'application
$app->boot();

return $app;
