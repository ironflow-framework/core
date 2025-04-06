<?php

declare(strict_types=1);

namespace IronFlow\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Closure;

/**
 * Classe Collection
 * 
 * Fournit une wrapper orienté objet pour travailler avec des tableaux de données
 * avec des méthodes utilitaires pour la manipulation de données.
 * 
 * @package IronFlow\Support
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Les éléments de la collection
     */
    protected array $items = [];

    /**
     * Crée une nouvelle collection
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Crée une nouvelle collection à partir d'un tableau
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Exécute une callback sur chaque élément
     */
    public function each(Closure $callback): static
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
    }

    /**
     * Applique une callback à chaque élément et retourne une nouvelle collection
     */
    public function map(Closure $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Filtre les éléments selon une callback
     */
    public function filter(Closure $callback = null): static
    {
        return new static($callback ? array_filter($this->items, $callback) : array_filter($this->items));
    }

    /**
     * Vérifie si un élément existe dans la collection
     */
    public function contains($value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * Retourne tous les éléments
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Implémentation de ArrayAccess
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Implémentation de Countable
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Implémentation de IteratorAggregate
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Implémentation de JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
