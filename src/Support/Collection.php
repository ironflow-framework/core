<?php

declare(strict_types=1);

namespace IronFlow\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

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
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Crée une nouvelle collection
     *
     * @param array $items Les éléments initiaux
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Crée une nouvelle collection à partir d'un tableau
     *
     * @param array $items Les éléments
     * @return static
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Ajoute un élément à la collection
     *
     * @param mixed $item L'élément à ajouter
     * @return $this
     */
    public function add(mixed $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Récupère tous les éléments
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Filtre les éléments avec un callback
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Transforme les éléments avec un callback
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Vérifie si la collection contient un élément
     *
     * @param mixed $key La clé ou valeur à chercher
     * @param mixed $value La valeur optionnelle si $key est une clé
     * @return bool
     */
    public function contains(mixed $key, mixed $value = null): bool
    {
        if (func_num_args() === 2) {
            return isset($this->items[$key]) && $this->items[$key] === $value;
        }

        return in_array($key, $this->items);
    }

    /**
     * Compte les éléments de la collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Récupère l'itérateur de la collection
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Vérifie si un offset existe
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Récupère un élément à un offset
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Définit un élément à un offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Supprime un élément à un offset
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Sérialise la collection en JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
