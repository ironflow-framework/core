<?php

declare(strict_types=1);

namespace IronFlow\Database;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
   protected array $items = [];

   public function __construct(array $items = [])
   {
      $this->items = $items;
   }

   public function all(): array
   {
      return $this->items;
   }

   public function first()
   {
      return !empty($this->items) ? reset($this->items) : null;
   }

   public function last()
   {
      return !empty($this->items) ? end($this->items) : null;
   }

   public function push($item): self
   {
      $this->items[] = $item;
      return $this;
   }

   public function put($key, $value): self
   {
      $this->items[$key] = $value;
      return $this;
   }

   public function has($key): bool
   {
      return array_key_exists($key, $this->items);
   }

   public function get($key, $default = null)
   {
      return $this->has($key) ? $this->items[$key] : $default;
   }

   public function forget($key): self
   {
      unset($this->items[$key]);
      return $this;
   }

   public function map(callable $callback): self
   {
      $result = [];
      foreach ($this->items as $key => $value) {
         $result[$key] = $callback($value, $key);
      }
      return new static($result);
   }

   public function filter(?callable $callback = null): self
   {
      if ($callback) {
         return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
      }
      return new static(array_filter($this->items));
   }

   public function pluck($value, $key = null): self
   {
      $results = [];
      foreach ($this->items as $item) {
         $itemValue = is_object($item) ? $item->{$value} : $item[$value];

         if ($key !== null) {
            $itemKey = is_object($item) ? $item->{$key} : $item[$key];
            $results[$itemKey] = $itemValue;
         } else {
            $results[] = $itemValue;
         }
      }

      return new static($results);
   }

   public function where($key, $operator, $value = null): self
   {
      if (func_num_args() === 2) {
         $value = $operator;
         $operator = '=';
      }

      return $this->filter(function ($item) use ($key, $operator, $value) {
         $retrievedValue = is_object($item) ? $item->{$key} : $item[$key] ?? null;

         switch ($operator) {
            case '=':
            case '==':
               return $retrievedValue == $value;
            case '!=':
            case '<>':
               return $retrievedValue != $value;
            case '>':
               return $retrievedValue > $value;
            case '>=':
               return $retrievedValue >= $value;
            case '<':
               return $retrievedValue < $value;
            case '<=':
               return $retrievedValue <= $value;
            default:
               return false;
         }
      });
   }

   public function sortBy($key, bool $descending = false): self
   {
      $items = $this->items;

      $sortFunction = $descending ? 'arsort' : 'asort';

      $sortFunction(array_map(function ($item) use ($key) {
         return is_object($item) ? $item->{$key} : $item[$key] ?? null;
      }, $items));

      $result = [];
      foreach (array_keys($items) as $idx) {
         $result[] = $this->items[$idx];
      }

      return new static($result);
   }

   public function keyBy($keyBy): self
   {
      $results = [];

      foreach ($this->items as $item) {
         $key = is_object($item) ? $item->{$keyBy} : $item[$keyBy] ?? null;
         $results[$key] = $item;
      }

      return new static($results);
   }

   public function take(int $limit): self
   {
      if ($limit < 0) {
         return new static(array_slice($this->items, $limit));
      }

      return new static(array_slice($this->items, 0, $limit));
   }

   public function merge($items): self
   {
      return new static(array_merge($this->items, $this->getArrayableItems($items)));
   }

   public function toArray(): array
   {
      return array_map(function ($item) {
         if (is_object($item) && method_exists($item, 'toArray')) {
            return $item->toArray();
         } elseif ($item instanceof JsonSerializable) {
            return $item->jsonSerialize();
         } elseif (is_object($item) && method_exists($item, 'getAttributes')) {
            return $item->getAttributes();
         }

         return $item;
      }, $this->items);
   }

   public function toJson(int $options = 0): string
   {
      return json_encode($this->jsonSerialize(), $options);
   }

   public function jsonSerialize(): mixed
   {
      return $this->toArray();
   }

   public function isEmpty(): bool
   {
      return empty($this->items);
   }

   public function isNotEmpty(): bool
   {
      return !$this->isEmpty();
   }

   public function count(): int
   {
      return count($this->items);
   }

   public function keys(): self
   {
      return new static(array_keys($this->items));
   }

   public function values(): self
   {
      return new static(array_values($this->items));
   }

   public function offsetExists(mixed $offset): bool
   {
      return isset($this->items[$offset]);
   }

   public function offsetGet(mixed $offset): mixed
   {
      return $this->items[$offset];
   }

   public function offsetSet(mixed $offset, mixed $value): void
   {
      if (is_null($offset)) {
         $this->items[] = $value;
      } else {
         $this->items[$offset] = $value;
      }
   }

   public function offsetUnset(mixed $offset): void
   {
      unset($this->items[$offset]);
   }

   public function getIterator(): Traversable
   {
      return new ArrayIterator($this->items);
   }

   protected function getArrayableItems($items): array
   {
      if (is_array($items)) {
         return $items;
      } elseif ($items instanceof self) {
         return $items->all();
      } elseif ($items instanceof JsonSerializable) {
         return $items->jsonSerialize();
      } elseif (is_object($items) && method_exists($items, 'toArray')) {
         return $items->toArray();
      }

      return (array) $items;
   }
}
