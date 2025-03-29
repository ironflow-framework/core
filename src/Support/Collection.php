<?php

declare(strict_types=1);

namespace IronFlow\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

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
    * @param array $items
    */
   public function __construct(array $items = [])
   {
      $this->items = $items;
   }

   /**
    * Crée une collection à partir d'un tableau
    *
    * @param array $items
    * @return static
    */
   public static function make(array $items = []): static
   {
      return new static($items);
   }

   /**
    * Récupère tous les éléments de la collection
    *
    * @return array
    */
   public function all(): array
   {
      return $this->items;
   }

   /**
    * Récupère un élément de la collection
    *
    * @param string|int $key
    * @param mixed $default
    * @return mixed
    */
   public function get(string|int $key, mixed $default = null): mixed
   {
      if ($this->has($key)) {
         return $this->items[$key];
      }

      return $default;
   }

   /**
    * Vérifie si un élément existe dans la collection
    *
    * @param string|int $key
    * @return bool
    */
   public function has(string|int $key): bool
   {
      return array_key_exists($key, $this->items);
   }

   /**
    * Ajoute un élément à la collection
    *
    * @param string|int|null $key
    * @param mixed $value
    * @return static
    */
   public function put(string|int|null $key, mixed $value): static
   {
      if ($key === null) {
         $this->items[] = $value;
      } else {
         $this->items[$key] = $value;
      }

      return $this;
   }

   /**
    * Supprime un élément de la collection
    *
    * @param string|int $key
    * @return static
    */
   public function forget(string|int $key): static
   {
      unset($this->items[$key]);
      return $this;
   }

   /**
    * Filtre la collection
    *
    * @param callable $callback
    * @return static
    */
   public function filter(callable $callback): static
   {
      return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
   }

   /**
    * Mappe la collection
    *
    * @param callable $callback
    * @return static
    */
   public function map(callable $callback): static
   {
      return new static(array_map($callback, $this->items));
   }

   /**
    * Trier la collection par les valeurs
    *
    * @param callable|null $callback
    * @return static
    */
   public function sort(callable $callback = null): static
   {
      $items = $this->items;

      if ($callback) {
         uasort($items, $callback);
      } else {
         asort($items);
      }

      return new static($items);
   }

   /**
    * Trier la collection par les clés
    *
    * @param callable|null $callback
    * @return static
    */
   public function sortKeys(callable $callback = null): static
   {
      $items = $this->items;

      if ($callback) {
         uksort($items, $callback);
      } else {
         ksort($items);
      }

      return new static($items);
   }

   /**
    * Renvoie la valeur de la première correspondance
    *
    * @param callable|null $callback
    * @param mixed $default
    * @return mixed
    */
   public function first(callable $callback = null, mixed $default = null): mixed
   {
      if (is_null($callback)) {
         if (empty($this->items)) {
            return $default;
         }

         foreach ($this->items as $item) {
            return $item;
         }
      }

      foreach ($this->items as $key => $value) {
         if ($callback($value, $key)) {
            return $value;
         }
      }

      return $default;
   }

   /**
    * Renvoie la valeur de la dernière correspondance
    *
    * @param callable|null $callback
    * @param mixed $default
    * @return mixed
    */
   public function last(callable $callback = null, mixed $default = null): mixed
   {
      if (is_null($callback)) {
         if (empty($this->items)) {
            return $default;
         }

         return end($this->items);
      }

      $items = array_reverse($this->items, true);

      foreach ($items as $key => $value) {
         if ($callback($value, $key)) {
            return $value;
         }
      }

      return $default;
   }

   /**
    * Convertit la collection en JSON
    *
    * @return string
    */
   public function toJson(): string
   {
      return json_encode($this->items);
   }

   /**
    * Implémentation de l'interface Countable
    *
    * @return int
    */
   public function count(): int
   {
      return count($this->items);
   }

   /**
    * Implémentation de l'interface ArrayAccess
    *
    * @param mixed $offset
    * @return bool
    */
   public function offsetExists(mixed $offset): bool
   {
      return $this->has($offset);
   }

   /**
    * Implémentation de l'interface ArrayAccess
    *
    * @param mixed $offset
    * @return mixed
    */
   public function offsetGet(mixed $offset): mixed
   {
      return $this->get($offset);
   }

   /**
    * Implémentation de l'interface ArrayAccess
    *
    * @param mixed $offset
    * @param mixed $value
    * @return void
    */
   public function offsetSet(mixed $offset, mixed $value): void
   {
      $this->put($offset, $value);
   }

   /**
    * Implémentation de l'interface ArrayAccess
    *
    * @param mixed $offset
    * @return void
    */
   public function offsetUnset(mixed $offset): void
   {
      $this->forget($offset);
   }

   /**
    * Implémentation de l'interface IteratorAggregate
    *
    * @return ArrayIterator
    */
   public function getIterator(): ArrayIterator
   {
      return new ArrayIterator($this->items);
   }

   /**
    * Implémentation de l'interface JsonSerializable
    *
    * @return array
    */
   public function jsonSerialize(): array
   {
      return $this->items;
   }
}
