<?php

declare(strict_types=1);

use IronFlow\Routing\Router;

$router = new Router();

// Routes API
$router->get('/api/health', function () {
   return ['status' => 'ok'];
});

return $router;
