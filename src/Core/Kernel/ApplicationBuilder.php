<?php

declare(strict_types=1);

namespace IronFlow\Core\Kernel;

// =============================================================================
// APPLICATION BUILDER - Pour une configuration fluide
// =============================================================================

/**
 * Builder pour configurer l'application de manière fluide
 */
final class ApplicationBuilder
{
    public function __construct(private Application $app) {}

    public function withRoutes(array|string $routes): self
    {
        $this->app->withRoutes($routes);
        return $this;
    }

    public function withModules(array $modules): self
    {
        $this->app->withModules($modules);
        return $this;
    }

    public function withProviders(array $providers): self
    {
        $this->app->withProviders($providers);
        return $this;
    }

    public function withMiddleware(array $middleware): self
    {
        $this->app->withMiddleware($middleware);
        return $this;
    }

    public function loadConfiguration(): self
    {
        $this->app->loadConfiguration();
        return $this;
    }

    public function build(): Application
    {
        return $this->app->boot();
    }

    public function autoDiscoverRoutes(): self
    {
        $this->app->autoDiscoverRoutes();
        return $this;
    }
}
