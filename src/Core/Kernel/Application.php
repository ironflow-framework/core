<?php

declare(strict_types=1);

namespace IronFlow\Core\Kernel;

use EnvironmentLoader;
use IronFlow\Core\Config\ConfigManager;
use IronFlow\Core\Container\Container;
use IronFlow\Core\Http\{Request, Response};
use IronFlow\Core\Http\Routing\Router;
use IronFlow\Core\Module\ModuleManager;
use IronFlow\Core\Provider\ProviderManager;
use IronFlow\Core\Exception\{HttpException, ApplicationException};
use IronFlow\Core\Exception\Handler\ExceptionHandler;
use IronFlow\Core\Event\EventDispatcher;
use IronFlow\Core\Http\Middleware\MiddlewarePipeline;

/**
 * IronFlow Application Kernel
 * 
 * Noyau principal du framework orchestrant l'initialisation,
 * le chargement des modules et le traitement des requêtes HTTP.
 */
final class Application
{
    private Container $container;
    private Router $router;
    private ModuleManager $moduleManager;
    private ProviderManager $providerManager;
    private ConfigManager $config;
    private EventDispatcher $events;
    private ExceptionHandler $exceptionHandler;

    private EnvironmentLoader $environment;

    private string $basePath;
    private array $globalMiddleware = [];
    private bool $booted = false;
    private bool $running = false;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath ?: $this->detectBasePath(), '/');
        $this->container = new Container();
        $this->environment = EnvironmentLoader::getInstance()->load($this->basePath . '/.env', ['.env', '.env.local']);

        $this->initializeCoreServices();
        $this->bindApplicationInstance();
    }

    /**
     * Création d'une instance d'application configurée
     */
    public static function create(string $basePath = ''): self
    {
        return new self($basePath);
    }

    /**
     * Configuration fluide de l'application
     */
    public static function configure(string $basePath = ''): ApplicationBuilder
    {
        return new ApplicationBuilder(new self($basePath));
    }

    /**
     * Initialise les services principaux du framework
     */
    private function initializeCoreServices(): void
    {
        $this->config = new ConfigManager($this->basePath);
        $this->events = new EventDispatcher();
        $this->router = new Router();
        $this->exceptionHandler = new ExceptionHandler();
        $this->moduleManager = new ModuleManager($this->container, $this->events);
        $this->providerManager = new ProviderManager($this->container, $this->events);
    }

    /**
     * Lie l'instance d'application dans le container
     */
    private function bindApplicationInstance(): void
    {
        $this->container->instance(self::class, $this);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Router::class, $this->router);
        $this->container->instance(ConfigManager::class, $this->config);
        $this->container->instance(EventDispatcher::class, $this->events);
        $this->container->instance(ModuleManager::class, $this->moduleManager);
        $this->container->instance(ProviderManager::class, $this->providerManager);
        $this->container->instance(ExceptionHandler::class, $this->exceptionHandler);
    }

    /**
     * Enregistre les routes de l'application
     */
    public function withRoutes(array|string $routes): self
    {
        $routeFiles = is_array($routes) ? $routes : [$routes];

        foreach ($routeFiles as $routeFile) {
            $this->loadRouteFile($routeFile);
        }

        return $this;
    }

    /**
     * Enregistre les modules de l'application
     */
    public function withModules(array $modules): self
    {
        foreach ($modules as $module) {
            $this->moduleManager->register($module);
        }

        return $this;
    }

    /**
     * Enregistre les service providers
     */
    public function withProviders(array $providers): self
    {
        foreach ($providers as $provider) {
            $this->providerManager->register($provider);
        }

        return $this;
    }

    /**
     * Enregistre les middlewares globaux
     */
    public function withMiddleware(array $middleware): self
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        return $this;
    }

    /**
     * Configure l'application à partir des fichiers de bootstrap
     */
    public function loadConfiguration(): self
    {
        $this->loadBootstrapFile('providers.php', fn($providers) => $this->withProviders($providers));
        $this->loadBootstrapFile('modules.php', fn($modules) => $this->withModules($modules));
        $this->loadBootstrapFile('middleware.php', fn($middleware) => $this->withMiddleware($middleware));
        $this->loadBootstrapFile('routes.php', fn($routes) => $this->withRoutes($routes));

        return $this;
    }

    /**
     * Boot l'application
     */
    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        }

        $this->events->dispatch('application.booting', $this);

        // Boot des service providers
        $this->providerManager->bootProviders();

        // Boot des modules
        $this->moduleManager->bootModules();

        $this->booted = true;
        $this->events->dispatch('application.booted', $this);

        return $this;
    }

    /**
     * Traite une requête HTTP
     */
    public function handle(Request $request): Response
    {
        if (!$this->booted) {
            $this->boot();
        }

        $this->running = true;

        try {
            $this->events->dispatch('request.received', $request);

            $response = $this->dispatchRequest($request);

            $this->events->dispatch('response.prepared', [$request, $response]);

            return $response;
        } catch (\Throwable $e) {
            return $this->handleException($e, $request);
        } finally {
            $this->running = false;
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->events->dispatch('Route chargé', $request, $response);
    }

    /**
     * Dispatch la requête vers le router
     */
    private function dispatchRequest(Request $request): Response
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());

        return match ($routeInfo[0]) {
            Router::FOUND => $this->handleFoundRoute($request, $routeInfo[1], $routeInfo[2]),
            Router::NOT_FOUND => throw new HttpException('Route not found', 404),
            Router::METHOD_NOT_ALLOWED => throw new HttpException('Method not allowed', 405),
            default => throw new ApplicationException('Invalid route dispatch result')
        };
    }

    /**
     * Traite une route trouvée
     */
    private function handleFoundRoute(Request $request, mixed $handler, array $params): Response
    {
        $request->setRouteParams($params);

        // Si $handler est un tableau avec une clé 'handler', on l'extrait (cas FastRoute)
        if (is_array($handler) && isset($handler['handler'])) {
            $handler = $handler['handler'];
        }

        return $this->executeMiddlewareStack($request, $handler);
    }

    /**
     * Exécute la pile de middleware
     */
    private function executeMiddlewareStack(Request $request, mixed $handler): Response
    {
        $pipeline = new MiddlewarePipeline($this->container);

        return $pipeline
            ->through($this->globalMiddleware)
            ->then(fn(Request $req) => $this->resolveHandler($handler, $req))
            ->process($request);
    }

    /**
     * Résout et exécute un handler
     */
    private function resolveHandler(mixed $handler, Request $request): Response
    {
        // Si le handler est une closure ou un callable simple, on l'appelle directement
        if (is_object($handler) && ($handler instanceof \Closure || is_callable($handler))) {
            $result = $handler($request);
        } else {
            // Sinon, on passe par le container (pour les contrôleurs de type [Class, 'method'] ou "Class@method")
            $result = $this->container->call($handler, ['request' => $request]);
        }

        return $result instanceof Response ? $result : new Response($result);
    }

    /**
     * Gestion des exceptions
     */
    private function handleException(\Throwable $exception, Request $request): Response
    {
        $this->events->dispatch('exception.occurred', [$exception, $request]);

        return $this->exceptionHandler->handle($exception, $request);
    }

    // Méthodes utilitaires

    private function loadRouteFile(string $routeFile): void
    {
        $path = $this->basePath . '/' . ltrim($routeFile, '/');

        if (!file_exists($path)) {
            throw new ApplicationException("Route file not found: {$path}");
        }

        $router = $this->router;
        require $path;
    }

    /**
     * Charge automatiquement tous les fichiers de routes dans /routes et /modules/*
     */
    public function autoDiscoverRoutes(): self
    {
        $routeFiles = [];
        // 1. /routes/*.php
        $routesDir = $this->basePath . '/routes';
        if (is_dir($routesDir)) {
            foreach (glob($routesDir . '/*.php') as $file) {
                $routeFiles[] = $file;
            }
        }
        // 2. /modules/*/routes/*.php
        $modulesDir = $this->basePath . '/modules';
        if (is_dir($modulesDir)) {
            foreach (glob($modulesDir . '/*/routes/*.php') as $file) {
                $routeFiles[] = $file;
            }
        }
        foreach ($routeFiles as $file) {
            $this->loadRouteFile(str_replace($this->basePath . '/', '', $file));
        }
        return $this;
    }


    private function loadBootstrapFile(string $filename, callable $callback): void
    {
        $path = $this->basePath . '/bootstrap/' . $filename;

        if (file_exists($path)) {
            $data = require $path;
            if (!empty($data)) {
                $callback($data);
            }
        }
    }

    private function detectBasePath(): string
    {
        return dirname(__DIR__, 4);
    }

    // Getters

    public function getContainer(): Container
    {
        return $this->container;
    }
    public function getRouter(): Router
    {
        return $this->router;
    }
    public function getConfig(): ConfigManager
    {
        return $this->config;
    }
    public function getEvents(): EventDispatcher
    {
        return $this->events;
    }
    public function getBasePath(): string
    {
        return $this->basePath;
    }
    public function isBooted(): bool
    {
        return $this->booted;
    }
    public function isRunning(): bool
    {
        return $this->running;
    }
}
