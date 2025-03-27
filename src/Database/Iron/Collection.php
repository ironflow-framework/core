<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron;

class Collection implements \ArrayAccess, \Iterator, \Countable
{
   protected array $items = [];
   protected int $position = 0;

   public function __construct(array $items = [])
   {
      $this->items = $items;
   }

   public function all(): array
   {
      return $this->items;
   }

   public function count(): int
   {
      return count($this->items);
   }

   public function isEmpty(): bool
   {
      return empty($this->items);
   }

   public function isNotEmpty(): bool
   {
      return !$this->isEmpty();
   }

   public function first()
   {
      return $this->items[0] ?? null;
   }

   public function last()
   {
      return $this->items[count($this->items) - 1] ?? null;
   }

   public function get($key, $default = null)
   {
      return $this->items[$key] ?? $default;
   }

   public function put($key, $value): self
   {
      $this->items[$key] = $value;
      return $this;
   }

   public function add($value): self
   {
      $this->items[] = $value;
      return $this;
   }

   public function push($value): self
   {
      return $this->add($value);
   }

   public function pop()
   {
      return array_pop($this->items);
   }

   public function shift()
   {
      return array_shift($this->items);
   }

   public function unshift($value): self
   {
      array_unshift($this->items, $value);
      return $this;
   }

   public function pull($key, $default = null)
   {
      $value = $this->get($key, $default);
      unset($this->items[$key]);
      return $value;
   }

   public function forget($key): self
   {
      unset($this->items[$key]);
      return $this;
   }

   public function has($key): bool
   {
      return isset($this->items[$key]);
   }

   public function contains($value): bool
   {
      return in_array($value, $this->items, true);
   }

   public function unique($key = null): self
   {
      if ($key === null) {
         return new static(array_unique($this->items));
      }

      $unique = [];
      foreach ($this->items as $item) {
         if (is_object($item)) {
            $keyValue = $item->$key;
         } else {
            $keyValue = $item[$key];
         }
         $unique[$keyValue] = $item;
      }

      return new static(array_values($unique));
   }

   public function values(): self
   {
      return new static(array_values($this->items));
   }

   public function keys(): self
   {
      return new static(array_keys($this->items));
   }

   public function map(callable $callback): self
   {
      return new static(array_map($callback, $this->items));
   }

   public function filter(?callable $callback = null): self
   {
      if ($callback === null) {
         return new static(array_filter($this->items));
      }

      return new static(array_filter($this->items, $callback));
   }

   public function where($key, $operator, $value = null): self
   {
      if ($value === null) {
         $value = $operator;
         $operator = '=';
      }

      return $this->filter(function ($item) use ($key, $operator, $value) {
         $itemValue = is_object($item) ? $item->$key : $item[$key];

         switch ($operator) {
            case '=':
               return $itemValue == $value;
            case '!=':
               return $itemValue != $value;
            case '>':
               return $itemValue > $value;
            case '>=':
               return $itemValue >= $value;
            case '<':
               return $itemValue < $value;
            case '<=':
               return $itemValue <= $value;
            case 'like':
               return strpos($itemValue, $value) !== false;
            case 'in':
               return in_array($itemValue, (array) $value);
            case 'not_in':
               return !in_array($itemValue, (array) $value);
            default:
               return false;
         }
      });
   }

   public function sort(callable $callback = null): self
   {
      $items = $this->items;
      if ($callback === null) {
         sort($items);
      } else {
         uasort($items, $callback);
      }
      return new static($items);
   }

   public function reverse(): self
   {
      return new static(array_reverse($this->items));
   }

   public function chunk(int $size): self
   {
      $chunks = [];
      foreach (array_chunk($this->items, $size) as $chunk) {
         $chunks[] = new static($chunk);
      }
      return new static($chunks);
   }

   public function pluck($value, $key = null): self
   {
      $results = [];
      foreach ($this->items as $item) {
         if (is_object($item)) {
            $itemValue = $item->$value;
            if ($key !== null) {
               $itemKey = $item->$key;
            }
         } else {
            $itemValue = $item[$value];
            if ($key !== null) {
               $itemKey = $item[$key];
            }
         }

         if ($key !== null) {
            $results[$itemKey] = $itemValue;
         } else {
            $results[] = $itemValue;
         }
      }

      return new static($results);
   }

   public function modelKeys(): array
   {
      return array_map(function ($model) {
         return $model->getKey();
      }, $this->items);
   }

   // ArrayAccess
   public function offsetExists($offset): bool
   {
      return isset($this->items[$offset]);
   }

   public function offsetGet($offset): mixed
   {
      return $this->items[$offset] ?? null;
   }

   public function offsetSet($offset, $value): void
   {
      if ($offset === null) {
         $this->items[] = $value;
      } else {
         $this->items[$offset] = $value;
      }
   }

   public function offsetUnset($offset): void
   {
      unset($this->items[$offset]);
   }

   // Iterator
   public function current(): mixed
   {
      return $this->items[$this->position];
   }

   public function key():mixed
   {
      return $this->position;
   }

   public function next(): void
   {
      $this->position++;
   }

   public function rewind(): void
   {
      $this->position = 0;
   }

   public function valid(): bool
   {
      return isset($this->items[$this->position]);
   }
}
