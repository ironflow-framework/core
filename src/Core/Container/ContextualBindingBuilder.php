<?php

declare(strict_types= 1);

namespace IronFlow\Core\Container;
class ContextualBindingBuilder
{
    protected Container $container;
    protected string $concrete;
    protected string $need;

    public function __construct(Container $container, string $concrete)
    {
        $this->container = $container;
        $this->concrete = $concrete;
    }

    public function needs(string $abstract): static
    {
        $this->need = $abstract;
        return $this;
    }

    public function give(mixed $implementation): void
    {
        $this->container->addContextualBinding($this->concrete, $this->need, $implementation);
    }
}