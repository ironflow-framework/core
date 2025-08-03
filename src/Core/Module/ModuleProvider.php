<?php

declare(strict_types=1);

namespace IronFlow\Core\Module;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Http\Router;

/**
 * Module Provider de base
 * 
 * Classe abstraite que tous les modules doivent étendre
 * pour s'intégrer au framework IronFlow.
 */
abstract class ModuleProvider
{
    protected Container $container;
    protected Router $router;

    /**
     * Enregistre les services du module dans le container
     */
    abstract public function register(Container $container): void;

    /**
     * Boot le module après l'enregistrement de tous les services
     */
    public function boot(Container $container): void
    {
        $this->container = $container;
        $this->router = $container->make(Router::class);
    }

    /**
     * Charge les routes d'un fichier
     */
    protected function loadRoutes(string $path): void
    {
        if (file_exists($path)) {
            $router = $this->router;
            require $path;
        }
    }

    /**
     * Charge les migrations d'un répertoire
     */
    protected function loadMigrationsFrom(string $path): void
    {
        // TODO: Implémenter le chargement des migrations
        // Cette méthode sera utilisée par le système de migration
    }

    /**
     * Charge les vues d'un répertoire
     */
    protected function loadViewsFrom(string $path, string $namespace): void
    {
        // TODO: Implémenter le chargement des vues
        // Sera utile si on ajoute un moteur de template
    }

    /**
     * Publie des assets (config, vues, etc.)
     */
    protected function publishes(array $paths, ?string $group = null): void
    {
        // TODO: Implémenter la publication d'assets
        // Permettra aux modules de publier des fichiers
    }

    /**
     * Enregistre des commandes CLI
     */
    protected function commands(array $commands): void
    {
        // TODO: Enregistrer les commandes dans le CLI kernel
        foreach ($commands as $command) {
            $this->container->singleton($command);
        }
    }
}