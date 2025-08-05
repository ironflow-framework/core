<?php

declare(strict_types=1);

namespace IronFlow\Core\Database\Relations;

use IronFlow\Core\Database\Model;

/**
 * Relation HasOne (Un à Un)
 */
class HasOne extends Relation
{
    public function getQuery()
    {
        $related = $this->newRelatedInstance();
        return $related::query()->where($this->foreignKey, $this->parent->getAttribute($this->localKey));
    }

    public function getResults(): ?Model
    {
        $result = $this->getQuery()->first();
        return $result ? $this->related::newFromBuilder($result) : null;
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
     * Sauvegarde un modèle existant avec cette relation
     */
    public function save(Model $model): bool
    {
        $model->setAttribute($this->foreignKey, $this->parent->getAttribute($this->localKey));
        return $model->save();
    }
}
