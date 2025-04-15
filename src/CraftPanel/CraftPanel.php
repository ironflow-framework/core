<?php

namespace IronFlow\CraftPanel;

use IronFlow\Database\Model;

class CraftPanel
{
    protected static array $registeredModels = [];
    protected static array $modelConfigs = [];

    public static function register(string $modelClass, array $config = []): void
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("$modelClass must be a subclass of " . Model::class);
        }

        static::$registeredModels[] = $modelClass;
        static::$modelConfigs[$modelClass] = array_merge([
            'displayName' => class_basename($modelClass),
            'fields' => [],
            'searchable' => [],
            'sortable' => [],
            'perPage' => 15,
        ], $config);
    }

    public static function getRegisteredModels(): array
    {
        return static::$registeredModels;
    }

    public static function getModelConfig(string $modelClass): ?array
    {
        return static::$modelConfigs[$modelClass] ?? null;
    }

    public static function isRegistered(string $modelClass): bool
    {
        return in_array($modelClass, static::$registeredModels);
    }
}
