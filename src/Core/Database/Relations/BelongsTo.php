<?php

declare(strict_types= 1);

namespace IronFlow\Core\Database\Relations;

use IronFlow\Core\Database\Model;

/**
 * Relation BelongsTo (Appartient à)
 */
class BelongsTo extends Relation
{
    protected string $ownerKey;

    public function __construct(Model $parent, string $related, ?string $foreignKey = null, ?string $ownerKey = null)
    {
        $this->ownerKey = $ownerKey ?? 'id';
        parent::__construct($parent, $related, $foreignKey, $this->ownerKey);
    }

    /**
     * Obtient la clé étrangère par défaut pour BelongsTo
     */
    protected function getForeignKey(): string
    {
        $relatedClass = (new \ReflectionClass($this->related))->getShortName();
        return strtolower($relatedClass) . '_id';
    }

    public function getQuery()
    {
        $related = $this->newRelatedInstance();
        return $related::query()->where($this->ownerKey, $this->parent->getAttribute($this->foreignKey));
    }

    public function getResults(): ?Model
    {
        $result = $this->getQuery()->first();
        return $result ? $this->related::newFromBuilder($result) : null;
    }

    /**
     * Associe ce modèle à un autre
     */
    public function associate(Model $model): Model
    {
        $this->parent->setAttribute($this->foreignKey, $model->getAttribute($this->ownerKey));
        return $this->parent;
    }

    /**
     * Dissocie ce modèle
     */
    public function dissociate(): Model
    {
        $this->parent->setAttribute($this->foreignKey, null);
        return $this->parent;
    }
}