<?php

declare(strict_types= 1);

namespace IronFlow\Core\Module;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Event\EventDispatcher;
use IronFlow\Core\Exception\ModuleException;

/**
 * Gestionnaire de modules avec support des dépendances et événements
 */
final class ModuleManager
{
    private array $modules = [];
    private array $booted = [];
    private array $dependencies = [];

    public function __construct(
        private Container $container,
        private EventDispatcher $events
    ) {}

    /**
     * Enregistre un module avec gestion des dépendances
     */
    public function register(string|ModuleProvider $module): void
    {
        if (is_string($module)) {
            $module = $this->container->make($module);
        }

        if (!$module instanceof ModuleProvider) {
            throw new ModuleException('Module must extend ModuleProvider');
        }

        $moduleClass = get_class($module);
        
        if (isset($this->modules[$moduleClass])) {
            return; // Déjà enregistré
        }

        $this->validateDependencies($module);
        $this->modules[$moduleClass] = $module;
        
        $this->events->dispatch('module.registering', $module);
        $module->register($this->container);
        $this->events->dispatch('module.registered', $module);
    }

    /**
     * Boot tous les modules dans l'ordre des dépendances
     */
    public function bootModules(): void
    {
        $bootOrder = $this->resolveDependencyOrder();
        
        foreach ($bootOrder as $moduleClass) {
            $this->bootModule($moduleClass);
        }
    }

    /**
     * Boot un module spécifique
     */
    private function bootModule(string $moduleClass): void
    {
        if (isset($this->booted[$moduleClass])) {
            return;
        }

        $module = $this->modules[$moduleClass];
        
        $this->events->dispatch('module.booting', $module);
        $module->boot($this->container);
        $this->booted[$moduleClass] = true;
        $this->events->dispatch('module.booted', $module);
    }

    /**
     * Valide les dépendances d'un module
     */
    private function validateDependencies(ModuleProvider $module): void
    {
        $dependencies = $module->getDependencies();
        $moduleClass = get_class($module);
        
        $this->dependencies[$moduleClass] = $dependencies;
        
        foreach ($dependencies as $dependency) {
            if (!isset($this->modules[$dependency]) && !class_exists($dependency)) {
                throw new ModuleException(
                    "Module {$moduleClass} depends on {$dependency} which is not available"
                );
            }
        }
    }

    /**
     * Résout l'ordre de boot basé sur les dépendances
     */
    private function resolveDependencyOrder(): array
    {
        $resolved = [];
        $unresolved = [];

        foreach (array_keys($this->modules) as $moduleClass) {
            $this->resolveDependencies($moduleClass, $resolved, $unresolved);
        }

        return $resolved;
    }

    private function resolveDependencies(string $moduleClass, array &$resolved, array &$unresolved): void
    {
        $unresolved[] = $moduleClass;

        foreach ($this->dependencies[$moduleClass] ?? [] as $dependency) {
            if (!in_array($dependency, $resolved)) {
                if (in_array($dependency, $unresolved)) {
                    throw new ModuleException("Circular dependency detected: {$moduleClass} -> {$dependency}");
                }
                $this->resolveDependencies($dependency, $resolved, $unresolved);
            }
        }

        $resolved[] = $moduleClass;
        $unresolved = array_diff($unresolved, [$moduleClass]);
    }

    // Getters et utilitaires
    public function getModules(): array { return $this->modules; }
    public function hasModule(string $moduleClass): bool { return isset($this->modules[$moduleClass]); }
    public function getModule(string $moduleClass): ?ModuleProvider { return $this->modules[$moduleClass] ?? null; }
}