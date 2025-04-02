<?php

namespace IronFlow\Support\Facades;

use IronFlow\Core\Application\Application;
use IronFlow\Routing\Router;
use IronFlow\Routing\RouterInterface;

class Route
{
    private static ?Router $router = null;

    private static function getRouter(): Router
    {
        if (self::$router === null) {
            self::$router = Application::getInstance()->getContainer()->make(RouterInterface::class);
        }
        return self::$router;
    }

    /**
     * Gère les appels statiques et les redirige vers la classe utilitaire
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return self::getRouter()->$method(...$arguments);
    }
}
