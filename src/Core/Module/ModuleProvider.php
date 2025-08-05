<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Module;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Event\EventDispatcher;
use IronFlow\Core\Http\Routing\Router;

/**
 * Provider de module avec support des métadonnées et dépendances
 */
abstract class ModuleProvider
{
    protected Container $container;
    protected EventDispatcher $events;

    /**
     * Informations sur le module
     */
    abstract public function getModuleInfo(): ModuleInfo;

    /**
     * Dépendances du module
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * Enregistrement des services du module
     */
    abstract public function register(Container $container): void;

    /**
     * Boot du module après enregistrement
     */
    public function boot(Container $container): void
    {
        $this->container = $container;
        $this->events = $container->make(EventDispatcher::class);
    }

    /**
     * Configuration du module
     */
    public function configure(): array
    {
        return [];
    }

    /**
     * Commandes CLI du module
     */
    public function getCommands(): array
    {
        return [];
    }

    /**
     * Middlewares du module
     */
    public function getMiddleware(): array
    {
        return [];
    }

    /**
     * Routes du module
     */
    public function getRoutes(): array
    {
        return [];
    }

    // Méthodes utilitaires

    protected function loadRoutes(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $router = $this->container->make(Router::class);
        require $path;
    }

    protected function publishConfig(string $source, string $destination): void
    {
        // TODO: Implémenter la publication de config
    }

    protected function loadMigrations(string $path): void
    {
        // TODO: Implémenter le chargement des migrations
    }

    protected function loadViews(string $path, string $namespace): void
    {
        // TODO: Implémenter le chargement des vues
    }
}