<?php

declare(strict_types= 1);

namespace IronFlow\Core\Database\Concerns;

/**
 * Trait pour la gestion des relations
 */
trait HasRelationships
{
    /**
     * Récupère toutes les relations chargées
     */
    public function getRelations(): array
    {
        return $this->relations ?? [];
    }

    /**
     * Définit une relation
     */
    public function setRelation(string $relation, mixed $value): self
    {
        $this->relations[$relation] = $value;
        return $this;
    }

    /**
     * Supprime une relation chargée
     */
    public function unsetRelation(string $relation): self
    {
        unset($this->relations[$relation]);
        return $this;
    }

    /**
     * Vérifie si une relation est chargée
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations ?? []);
    }

    /**
     * Charge plusieurs relations
     */
    public function loadMissing(array $relations): self
    {
        foreach ($relations as $relation) {
            if (!$this->relationLoaded($relation)) {
                $this->load($relation);
            }
        }
        return $this;
    }
}