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
}
