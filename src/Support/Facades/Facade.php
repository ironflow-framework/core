<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use RuntimeException;

abstract class Facade
{
    /**
     * Récupère l'instance de la façade
     */
    protected static array $resolvedInstances = [];

    /**
     * Gère les appels de méthodes statiques
     */
    protected static function getFacadeAccessor(): string
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    protected static function getFacadeInstance()
    {
        $name = static::getFacadeAccessor();

        if (!isset(static::$resolvedInstances[$name])) {
            static::$resolvedInstances[$name] = new $name();
        }

        return static::$resolvedInstances[$name];
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeInstance();

        return $instance->$method(...$args);
    }
}
