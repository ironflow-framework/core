<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Container\Container;

/**
 * Pipeline de middleware avec une API fluide
 */
final class MiddlewarePipeline
{
    private array $middleware = [];

    public function __construct(private Container $container) {}

    public function through(array $middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }

    public function then(callable $destination): MiddlewareStack
    {
        return new MiddlewareStack($this->container, $this->middleware, $destination);
    }
}
