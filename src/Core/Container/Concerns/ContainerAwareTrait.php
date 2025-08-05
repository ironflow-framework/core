<?php

declare(strict_types=1);

namespace Ironflow\Core\Container\Concerns;

use IronFlow\Core\Container\Container;

trait ContainerAwareTrait
{
    protected $container;
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        if (!$this->hasContainer()) {
            throw new \RuntimeException('Container is not set.');
        }
        return $this->container;
    }
}