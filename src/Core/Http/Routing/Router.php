<?php

declare(strict_types=1);

namespace IronFlow\Core\Http\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Routeur HTTP basé sur FastRoute
 * 
 * Système de routage rapide et flexible avec support
 * des groupes, middleware, et paramètres de route.
 */
class Router
{
    public const NOT_FOUND = Dispatcher::NOT_FOUND;
    public const METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;
    public const FOUND = Dispatcher::FOUND;

    private array $routes = [];
    private array $routeGroups = [];
    private array $currentGroupStack = [];
    private ?Dispatcher $dispatcher = null;

    /**
     * Enregistre une route GET
     */
    public function get(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['GET'], $uri, $handler);
    }

    /**
     * Enregistre une route POST
     */
    public function post(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['POST'], $uri, $handler);
    }

    /**
     * Enregistre une route PUT
     */
    public function put(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['PUT'], $uri, $handler);
    }

    /**
     * Enregistre une route PATCH
     */
    public function patch(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['PATCH'], $uri, $handler);
    }

    /**
     * Enregistre une route DELETE
     */
    public function delete(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['DELETE'], $uri, $handler);
    }

    /**
     * Enregistre une route OPTIONS
     */
    public function options(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['OPTIONS'], $uri, $handler);
    }

    /**
     * Enregistre une route pour plusieurs méthodes HTTP
     */
    public function match(array $methods, string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(array_map('strtoupper', $methods), $uri, $handler);
    }

    /**
     * Enregistre une route pour toutes les méthodes HTTP
     */
    public function any(string $uri, callable|string|array $handler): RouteRegistration
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $uri, $handler);
    }

    /**
     * Crée un groupe de routes avec préfixe et/ou middleware communs
     */
    public function group(array $attributes, \Closure $routes): void
    {
        $this->currentGroupStack[] = $attributes;
        $routes($this);
        array_pop($this->currentGroupStack);
    }

    /**
     * Ajoute une route au registre
     */
    private function addRoute(array $methods, string $uri, callable|string|array $handler): RouteRegistration
    {
        $uri = $this->buildGroupUri($uri);
        $middleware = $this->buildGroupMiddleware();
        
        $route = new RouteRegistration($methods, $uri, $handler, $middleware);
        $this->routes[] = $route;
        
        // Invalide le dispatcher pour forcer la reconstruction
        $this->dispatcher = null;
        
        return $route;
    }

    /**
     * Construit l'URI complète avec les préfixes de groupe
     */
    private function buildGroupUri(string $uri): string
    {
        $prefix = '';
        
        foreach ($this->currentGroupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        
        return rtrim($prefix . '/' . ltrim($uri, '/'), '/') ?: '/';
    }

    /**
     * Construit la liste des middleware avec ceux des groupes
     */
    private function buildGroupMiddleware(): array
    {
        $middleware = [];
        
        foreach ($this->currentGroupStack as $group) {
            if (isset($group['middleware'])) {
                $groupMiddleware = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $middleware = array_merge($middleware, $groupMiddleware);
            }
        }
        
        return $middleware;
    }

    /**
     * Dispatch une requête HTTP
     */
    public function dispatch(string $method, string $uri): array
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = $this->createDispatcher();
        }

        return $this->dispatcher->dispatch($method, $uri);
    }

    /**
     * Crée le dispatcher FastRoute
     */
    private function createDispatcher(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                foreach ($route->getMethods() as $method) {
                    $r->addRoute($method, $route->getUri(), [
                        'handler' => $route->getHandler(),
                        'middleware' => $route->getMiddleware()
                    ]);
                }
            }
        });
    }

    /**
     * Charge des routes depuis un fichier
     */
    public function loadRoutes(string $path): void
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Route file not found: {$path}");
        }

        // Le fichier de routes aura accès à $this (le router)
        $router = $this;
        require $path;
    }

    /**
     * Retourne toutes les routes enregistrées
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
