<?php

declare(strict_types= 1);

namespace IronFlow\Core\Database\Concerns;

/**
 * Trait pour la gestion des attributs
 */
trait HasAttributes
{
    /**
     * Récupère tous les attributs
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Définit tous les attributs
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Vérifie si un attribut existe
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Supprime un attribut
     */
    public function unsetAttribute(string $key): self
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Ajoute un attribut sans vérifier fillable
     */
    public function forceFill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }
}