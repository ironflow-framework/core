<?php

declare(strict_types= 1);

namespace IronFlow\Core\Container\Concerns;

interface ContainerAwareInterface
{
    /**
     * Set the container instance.
     *
     * @param \IronFlow\Core\Container\Container $container
     */
    public function setContainer(\IronFlow\Core\Container\Container $container): void;

    /**
     * Get the container instance.
     *
     * @return \IronFlow\Core\Container\Container
     */
    public function getContainer(): \IronFlow\Core\Container\Container;

    /**
     * Check if the container is set.
     *
     * @return bool
     */
    public function hasContainer(): bool;

    
}