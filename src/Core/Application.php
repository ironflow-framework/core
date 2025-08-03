<?php

declare(strict_types=1);

namespace IronFlow\Core;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Http\Router;
use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;
use IronFlow\Core\Module\ModuleProvider;
use IronFlow\Core\Exception\HttpException;
use IronFlow\Core\Exception\Handler\ExceptionHandler;

/**
 * IronFlow Application Kernel
 * 
 * Le noyau principal du framework qui orchestre l'initialisation,
 * le chargement des modules et le traitement des requêtes HTTP.
 */
class Application
{
    private Container $container;
    private Router $router;
    private string $base_path;
    private array $modules = [];
    private array $middleware = [];
    private ExceptionHandler $exceptionHandler;
    private bool $booted = false;

    public function __construct(Container $container, string $base_path = '')
    {
        $this->container = $container;
        $this->router = new Router();
        $this->exceptionHandler = new ExceptionHandler();
        $this->base_path = $base_path;

        $this->registerCoreServices();
    }

    public static function getInstance(): self
    {
        static $instance = null;
        if ($instance === null) {
            $container = new Container();
            $basePath = rtrim(__DIR__ . '/../../../../', '/') . '/';
            $instance = new self($container, $basePath);
        }
        return $instance;
    }

    /**
     * Enregistre les services de base du framework
     */
    private function registerCoreServices(): void
    {
        $this->container->singleton(Router::class, fn() => $this->router);
        $this->container->singleton(Container::class, fn() => $this->container);
        $this->container->singleton(ExceptionHandler::class, fn() => $this->exceptionHandler);
    }

    /**
     * Enregistre un module dans l'application
     */
    public function registerModule(ModuleProvider $module): self
    {
        $this->modules[] = $module;
        return $this;
    }

    /**
     * Ajoute un middleware global
     */
    public function addMiddleware(string $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Boot l'application (charge les modules et configure les services)
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Boot tous les modules enregistrés
        foreach ($this->modules as $module) {
            $module->register($this->container);
            $module->boot($this->container);
        }

        $this->booted = true;
    }

    /**
     * Traite une requête HTTP et retourne une réponse
     */
    public function handle(Request $request)
    {
        try {
            $this->boot();

            // Résolution de la route
            $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());
            
            switch ($routeInfo[0]) {
                case Router::NOT_FOUND:
                    throw new HttpException('Route not found', 404);
                
                case Router::METHOD_NOT_ALLOWED:
                    throw new HttpException('Method not allowed', 405);
                
                case Router::FOUND:
                    [$handler, $vars] = [$routeInfo[1], $routeInfo[2]];
                    $request->setRouteParams($vars);
                    
                    return $this->executeMiddlewareChain($request, $handler);
            }

        } catch (\Throwable $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Exécute la chaîne de middleware et le handler final
     */
    private function executeMiddlewareChain(Request $request, callable $handler): Response
    {
        $pipeline = array_reverse($this->middleware);
        
        $next = function(Request $request) use ($handler): Response {
            return $this->resolveHandler($handler, $request);
        };

        // Construction de la pipeline de middleware
        foreach ($pipeline as $middleware) {
            $next = function(Request $request) use ($middleware, $next): Response {
                $middlewareInstance = $this->container->make($middleware);
                return $middlewareInstance->handle($request, $next);
            };
        }

        return $next($request);
    }

    /**
     * Résout et exécute un handler (contrôleur, closure, etc.)
     */
    private function resolveHandler(callable|string|array $handler, Request $request): Response
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controllerInstance = $this->container->make($controller);
            $result = $controllerInstance->$method($request);
        } elseif (is_array($handler)) {
            [$controller, $method] = $handler;
            $controllerInstance = $this->container->make($controller);
            $result = $controllerInstance->$method($request);
        } else {
            $result = $handler($request);
        }

        return $result instanceof Response ? $result : new Response($result);
    }

    /**
     * Retourne le container DI
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Retourne le router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Retourne le chemin de base de l'application
     */
    public function getBasePath(): string
    {
        return rtrim($this->base_path, '/') . '/';
    }
}