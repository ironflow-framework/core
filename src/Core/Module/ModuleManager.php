<?php

declare(strict_types=1);

namespace IronFlow\Core\Module;

use IronFlow\Core\Container\Container;

/**
 * Gestionnaire de modules
 */
class ModuleManager
{
    private Container $container;
    private array $modules = [];
    private array $booted = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Enregistre un module
     */
    public function register(ModuleProvider $module): void
    {
        $moduleClass = get_class($module);
        
        if (!isset($this->modules[$moduleClass])) {
            $this->modules[$moduleClass] = $module;
            $module->register($this->container);
        }
    }

    /**
     * Boot tous les modules enregistrés
     */
    public function bootModules(): void
    {
        foreach ($this->modules as $moduleClass => $module) {
            if (!isset($this->booted[$moduleClass])) {
                $module->boot($this->container);
                $this->booted[$moduleClass] = true;
            }
        }
    }

    /**
     * Obtient tous les modules enregistrés
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Vérifie si un module est enregistré
     */
    public function hasModule(string $moduleClass): bool
    {
        return isset($this->modules[$moduleClass]);
    }
}