<?php

declare(strict_types= 1);

namespace Ironflow\Core\Src\Core\Http\Routing;


/**
 * Classe représentant l'enregistrement d'une route
 */
class RouteRegistration
{
    private array $methods;
    private string $uri;
    private callable|string|array $handler;
    private array $middleware;
    private ?string $name = null;

    public function __construct(array $methods, string $uri, callable|string|array $handler, array $middleware = [])
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    /**
     * Ajoute un middleware à cette route
     */
    public function middleware(string|array $middleware): self
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    /**
     * Donne un nom à cette route
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // Getters
    public function getMethods(): array { return $this->methods; }
    public function getUri(): string { return $this->uri; }
    public function getHandler(): callable|string|array { return $this->handler; }
    public function getMiddleware(): array { return $this->middleware; }
    public function getName(): ?string { return $this->name; }
}