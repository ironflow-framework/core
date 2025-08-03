<?php

declare(strict_types=1);

namespace IronFlow\Core\Database\Relations;

use IronFlow\Core\Database\Model;
use IronFlow\Core\Database\Collection;

/**
 * Relation de base
 */
abstract class Relation
{
    protected Model $parent;
    protected string $related;
    protected string $foreignKey;
    protected string $localKey;

    public function __construct(Model $parent, string $related, ?string $foreignKey = null, ?string $localKey = null)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey ?? $this->getForeignKey();
        $this->localKey = $localKey ?? $this->getLocalKey();
    }

    /**
     * Obtient la clé étrangère par défaut
     */
    protected function getForeignKey(): string
    {
        $parentClass = (new \ReflectionClass($this->parent))->getShortName();
        return strtolower($parentClass) . '_id';
    }

    /**
     * Obtient la clé locale par défaut
     */
    protected function getLocalKey(): string
    {
        return 'id';
    }

    /**
     * Crée une nouvelle instance du modèle lié
     */
    protected function newRelatedInstance(): Model
    {
        return new $this->related();
    }

    /**
     * Obtient la requête de base pour cette relation
     */
    abstract public function getQuery();

    /**
     * Obtient les résultats de la relation
     */
    abstract public function getResults();

    /**
     * Ajoute des contraintes WHERE supplémentaires
     */
    public function where(string $column, string $operator = '=', mixed $value = null): static
    {
        return $this->getQuery()->where($column, $operator, $value);
    }

    /**
     * Ordonne les résultats
     */
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        return $this->getQuery()->orderBy($column, $direction);
    }
}

