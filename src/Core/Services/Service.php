<?php

declare(strict_types=1);

namespace IronFlow\Core\Services;

use IronFlow\Core\Container\Container;

/**
 * Service de base pour IronFlow
 * 
 * Fournit l'accès au container et l'injection automatique
 * des autres services et models.
 */
abstract class Service
{
    protected Container $container;
    
    /**
     * Cache des services et models injectés
     */
    private array $dependencyCache = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Injection automatique des dépendances via propriété magique
     * 
     * Conventions supportées :
     * - $this->postService => App\Services\PostService
     * - $this->userModel => App\Models\User
     * - $this->emailService => App\Services\EmailService
     * 
     * @param string $name Nom de la propriété
     * @return mixed La dépendance résolue
     * @throws \RuntimeException Si la dépendance n'est pas trouvée
     */
    public function __get(string $name)
    {
        // Vérifier le cache d'abord
        if (isset($this->dependencyCache[$name])) {
            return $this->dependencyCache[$name];
        }

        $dependency = null;

        // Déterminer le type de dépendance et la résoudre
        if (str_ends_with($name, 'Service')) {
            $dependency = $this->resolveService($name);
        } elseif (str_ends_with($name, 'Model')) {
            $dependency = $this->resolveModel($name);
        } else {
            // Tentative générique
            $dependency = $this->resolveGeneric($name);
        }

        if ($dependency === null) {
            throw new \RuntimeException("Dépendance non trouvée pour la propriété '{$name}'");
        }

        // Mettre en cache
        $this->dependencyCache[$name] = $dependency;
        return $dependency;
    }

    /**
     * Résout un service
     */
    private function resolveService(string $name): ?object
    {
        $serviceClass = $this->convertToPascalCase($name);
        
        $possibleClasses = [
            "App\\Services\\{$serviceClass}",
            "IronFlow\\Core\\Services\\{$serviceClass}",
            $serviceClass
        ];

        foreach ($possibleClasses as $className) {
            if ($this->container->has($className)) {
                return $this->container->make($className);
            }
        }

        return null;
    }

    /**
     * Résout un model
     */
    private function resolveModel(string $name): ?object
    {
        // Convertir 'userModel' => 'User'
        $modelName = str_replace('Model', '', $this->convertToPascalCase($name));
        
        $possibleClasses = [
            "App\\Models\\{$modelName}",
            "IronFlow\\Core\\Models\\{$modelName}",
            $modelName
        ];

        foreach ($possibleClasses as $className) {
            if ($this->container->has($className) || class_exists($className)) {
                return $this->container->has($className) 
                    ? $this->container->make($className)
                    : new $className();
            }
        }

        return null;
    }

    /**
     * Résolution générique
     */
    private function resolveGeneric(string $name): ?object
    {
        $className = $this->convertToPascalCase($name);
        
        $possibleClasses = [
            "App\\Services\\{$className}",
            "App\\Models\\{$className}",
            "IronFlow\\Core\\Services\\{$className}",
            "IronFlow\\Core\\Models\\{$className}",
            $className
        ];

        foreach ($possibleClasses as $class) {
            if ($this->container->has($class)) {
                return $this->container->make($class);
            }
            if (class_exists($class)) {
                return new $class();
            }
        }

        return null;
    }

    /**
     * Convertit camelCase vers PascalCase
     */
    private function convertToPascalCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * Vérifie si une dépendance existe
     */
    public function __isset(string $name): bool
    {
        if (isset($this->dependencyCache[$name])) {
            return true;
        }

        try {
            $this->__get($name);
            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    /**
     * Accès direct au container
     */
    protected function get(string $service)
    {
        return $this->container->make($service);
    }

    /**
     * Injection manuelle d'une dépendance (tests)
     */
    public function setDependency(string $name, $dependency): void
    {
        $this->dependencyCache[$name] = $dependency;
    }

    /**
     * Vide le cache des dépendances
     */
    public function clearDependencyCache(): void
    {
        $this->dependencyCache = [];
    }
    
}