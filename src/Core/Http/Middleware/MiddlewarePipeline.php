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
        if (empty($this->middleware)) {
            return new MiddlewareStack($this->container, [], $destination);
        }

        // Convert middleware class names to instances
        foreach ($this->middleware as $key => $middleware) {
            if (is_string($middleware)) {
                $this->middleware[$key] = $this->container->make($middleware);
            }
        }

        // Return a new MiddlewareStack with the configured middleware and destination
        return new MiddlewareStack($this->container, $this->middleware, $destination);
    }    

    public function to(callable $destination): MiddlewareStack
    {
        return new MiddlewareStack($this->container, $this->middleware, $destination);
    }
}
