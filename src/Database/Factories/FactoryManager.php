<?php 

namespace IronFlow\Database\Factories;

use IronFlow\Database\Exceptions\FactoryException;

/**
 * Gestionnaire global des factories
 */
class FactoryManager
{
    protected static array $factories = [];
    protected static array $modelFactories = [];

    /**
     * Enregistre une factory pour un modèle
     */
    public static function register(string $model, string $factory): void
    {
        static::$modelFactories[$model] = $factory;
    }

    /**
     * Récupère une factory pour un modèle
     */
    public static function factoryForModel(string $model): Factory
    {
        if (!isset(static::$modelFactories[$model])) {
            throw new FactoryException("No factory registered for model {$model}");
        }

        $factoryClass = static::$modelFactories[$model];

        if (!isset(static::$factories[$factoryClass])) {
            static::$factories[$factoryClass] = new $factoryClass();
        }

        return clone static::$factories[$factoryClass];
    }

    /**
     * Crée une instance de factory
     */
    public static function factory(string $model): Factory
    {
        return static::factoryForModel($model);
    }
}
