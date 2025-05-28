<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeders;

use IronFlow\Database\Exceptions\SeederException;

/**
 * Seeder avec support pour les factories
 */
abstract class ModelSeeder extends Seeder
{
    protected string $model;
    protected string $factory;
    protected int $count = 10;

    /**
     * Crée des instances du modèle via la factory
     */
    protected function createModels(?int $count = null, array $attributes = []): array
    {
        $count = $count ?? $this->count;

        if (!isset($this->factory)) {
            throw new SeederException("Factory not defined for " . static::class);
        }

        $factory = new $this->factory();
        return $factory->createMany($count, $attributes);
    }

    /**
     * Crée une seule instance du modèle
     */
    protected function createModel(array $attributes = []): object
    {
        if (!isset($this->factory)) {
            throw new SeederException("Factory not defined for " . static::class);
        }

        $factory = new $this->factory();
        return $factory->create($attributes);
    }
}