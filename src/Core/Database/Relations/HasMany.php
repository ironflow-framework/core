<?php

declare(strict_types= 1);

namespace IronFlow\Core\Database\Relations;

use IronFlow\Core\Database\Collection;
use IronFlow\Core\Database\Model;
use IronFlow\Core\Database\QueryBuilder;
use IronFlow\Core\Database\Relations\Relation;

/**
 * Relation HasMany (Un à Plusieurs)
 */
class HasMany extends Relation
{
    public function getQuery(): QueryBuilder
    {
        $related = $this->newRelatedInstance();
        return $related::query()->where($this->foreignKey, $this->parent->getAttribute($this->localKey));
    }

    public function getResults(): Collection
    {
        $results = $this->getQuery()->get();
        if ($results instanceof Collection) {
            return $results->map(fn($item) => is_array($item) ? $this->related::newFromBuilder($item) : $item);
        }
        return collect($results)->map(fn($item) => is_array($item) ? $this->related::newFromBuilder($item) : $item);
    }

    /**
     * Compte les résultats
     */
    public function count(): int
    {
        return $this->getQuery()->count();
    }

    /**
     * Crée un nouveau modèle lié
     */
    public function create(array $attributes): Model
    {
        $attributes[$this->foreignKey] = $this->parent->getAttribute($this->localKey);
        return $this->related::create($attributes);
    }

    /**
     * Crée plusieurs modèles liés
     */
    public function createMany(array $records): Collection
    {
        $models = collect([]);
        foreach ($records as $attributes) {
            $models->push($this->create($attributes));
        }
        return $models;
    }

    /**
     * Sauvegarde un modèle existant avec cette relation
     */
    public function save(Model $model): bool
    {
        $model->setAttribute($this->foreignKey, $this->parent->getAttribute($this->localKey));
        return $model->save();
    }

    /**
     * Sauvegarde plusieurs modèles
     */
    public function saveMany(array $models): bool
    {
        $success = true;
        foreach ($models as $model) {
            if (!$this->save($model)) {
                $success = false;
            }
        }
        return $success;
    }
}