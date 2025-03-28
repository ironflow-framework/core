<?php

declare(strict_types=1);

namespace IronFlow\Application;

use Closure;

class Container
{
   private array $bindings = [];

   public function singleton(string $name, Closure $callback): void
   {
      $this->bindings[$name] = $callback;
   }

   public function get(string $name): mixed
   {
      return $this->bindings[$name]();
   }

   public function set(string $name, mixed $value): void
   {
      $this->bindings[$name] = $value;
   }

   public function has(string $name): bool
   {
      return isset($this->bindings[$name]);
   }

   public function remove(string $name): void
   {
      unset($this->bindings[$name]);
   }

   public function clear(): void
   {
      $this->bindings = [];
   }

   public function count(): int
   {
      return count($this->bindings);
   }

}
