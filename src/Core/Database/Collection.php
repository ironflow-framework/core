<?php

declare(strict_types=1);

namespace IronFlow\Core\Database;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

/**
 * Collection - Classe pour manipuler des collections de données
 */
class Collection implements ArrayAccess, Countable, Iterator, JsonSerializable
{
    protected array $items = [];
    protected int $position = 0;

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    /**
     * Crée une nouvelle collection
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Convertit en tableau
     */
    public function toArray(): array
    {
        return array_map(function ($item) {
            return $item instanceof JsonSerializable ? $item->jsonSerialize() : $item;
        }, $this->items);
    }

    /**
     * Première élément
     */
    public function first(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items[0] ?? null;
        }

        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Dernier élément
     */
    public function last(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return end($this->items) ?: null;
        }

        $filtered = array_filter($this->items, $callback);
        return end($filtered) ?: null;
    }

    /**
     * Filtre la collection
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback === null) {
            return new static(array_filter($this->items));
        }

        return new static(array_filter($this->items, $callback));
    }

    /**
     * Mappe les éléments
     */
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Réduit la collection
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Pluck - Extrait les valeurs d'une clé
     */
    public function pluck(string $key, ?string $keyBy = null): static
    {
        $results = [];

        foreach ($this->items as $item) {
            $value = is_object($item) ? $item->$key : $item[$key];
            
            if ($keyBy === null) {
                $results[] = $value;
            } else {
                $keyValue = is_object($item) ? $item->$keyBy : $item[$keyBy];
                $results[$keyValue] = $value;
            }
        }

        return new static($results);
    }

    /**
     * Groupe par clé
     */
    public function groupBy(string $key): static
    {
        $groups = [];

        foreach ($this->items as $item) {
            $keyValue = is_object($item) ? $item->$key : $item[$key];
            $groups[$keyValue][] = $item;
        }

        return new static($groups);
    }

    /**
     * Tri par clé
     */
    public function sortBy(string $key, bool $descending = false): static
    {
        $items = $this->items;

        usort($items, function ($a, $b) use ($key, $descending) {
            $aValue = is_object($a) ? $a->$key : $a[$key];
            $bValue = is_object($b) ? $b->$key : $b[$key];

            if ($aValue == $bValue) {
                return 0;
            }

            $result = ($aValue < $bValue) ? -1 : 1;
            return $descending ? -$result : $result;
        });

        return new static($items);
    }

    /**
     * Reverse la collection
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->items));
    }

    /**
     * Prend les X premiers éléments
     */
    public function take(int $count): static
    {
        return new static(array_slice($this->items, 0, $count));
    }

    /**
     * Ignore les X premiers éléments
     */
    public function skip(int $count): static
    {
        return new static(array_slice($this->items, $count));
    }

    /**
     * Slice de la collection
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length));
    }

    /**
     * Chunk - Divise en blocs
     */
    public function chunk(int $size): static
    {
        $chunks = [];
        
        for ($i = 0; $i < count($this->items); $i += $size) {
            $chunks[] = new static(array_slice($this->items, $i, $size));
        }

        return new static($chunks);
    }

    /**
     * Unique - Supprime les doublons
     */
    public function unique(?string $key = null): static
    {
        if ($key === null) {
            return new static(array_unique($this->items));
        }

        $used = [];
        $items = [];

        foreach ($this->items as $item) {
            $keyValue = is_object($item) ? $item->$key : $item[$key];
            
            if (!in_array($keyValue, $used)) {
                $used[] = $keyValue;
                $items[] = $item;
            }
        }

        return new static($items);
    }

    /**
     * Merge avec une autre collection
     */
    public function merge(array|Collection $items): static
    {
        $mergeItems = $items instanceof Collection ? $items->toArray() : $items;
        return new static(array_merge($this->items, $mergeItems));
    }

    /**
     * Concat avec une autre collection
     */
    public function concat(array|Collection $items): static
    {
        return $this->merge($items);
    }

    /**
     * Push un élément
     */
    public function push(mixed $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Pop le dernier élément
     */
    public function pop(): mixed
    {
        return array_pop($this->items);
    }

    /**
     * Shift le premier élément
     */
    public function shift(): mixed
    {
        return array_shift($this->items);
    }

    /**
     * Unshift un élément au début
     */
    public function unshift(mixed $item): static
    {
        array_unshift($this->items, $item);
        return $this;
    }

    /**
     * Recherche un élément
     */
    public function search(mixed $value): int|string|false
    {
        return array_search($value, $this->items);
    }

    /**
     * Vérifie si contient un élément
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items);
    }

    /**
     * ForEach
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Vérifie si vide
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Vérifie si non vide
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Compte les éléments
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Récupère les clés
     */
    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    /**
     * Récupère les valeurs
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Conversion JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    /**
     * Pour json_encode
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ===========================================
    // INTERFACES
    // ===========================================

    // ArrayAccess
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    // Iterator
    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * Conversion en string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}

// ===========================================
// FONCTION HELPER GLOBALE
// ===========================================

if (!function_exists('collect')) {
    /**
     * Helper pour créer une collection
     */
    function collect(array $items = []): Collection
    {
        return new Collection($items);
    }
}