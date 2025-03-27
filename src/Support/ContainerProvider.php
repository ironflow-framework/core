<?php

declare(strict_types=1);

namespace IronFlow\Support;

use IronFlow\Application\Container;

abstract class ContainerProvider
{
   protected Container $container;

   public function __construct(Container $container)
   {
      $this->container = $container;
   }

   public function get(string $name): mixed
   {
      return $this->container->get($name);
   }
}
