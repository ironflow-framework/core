<?php

declare(strict_types=1);

namespace IronFlow\Core\Container;

use IronFlow\Core\Exception\Container\ContainerException;
use IronFlow\Core\Exception\Container\NotFoundException;
use IronFlow\Core\Services\Concernes\ServiceInterface;
use ReflectionClass;
use Closure;
use IronFlow\Core\Container\Concernes\ProviderInterface;
use IronFlow\Core\Providers\Concernes\ServiceProviderInterface;
use IronFlow\Core\Services\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container d'injection de dépendances
 * 
 * Un container DI puissant avec auto-wiring, singletons,
 * et résolution automatique des dépendances.
 */
class Container implements ContainerInterface
{

    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];
    private array $parameters = [];
    private bool $initialized = false;

    public function set(string $id, mixed $value): void
    {
        $this->instance($id, $value);
    }

    /**
     * {@inheritdoc}
     * Compatible avec symfony/dependency-injection
     */
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): object|null
    {
        if (!$this->has($id)) {
            if ($invalidBehavior === self::EXCEPTION_ON_INVALID_REFERENCE) {
                throw new NotFoundException("Service '{$id}' not found");
            }
            return null;
        }
        return $this->make($id);
    }
    /**
     * Supprime un service ou une instance du container (optionnel, non dans l'interface Symfony)
     */
    public function remove(string $id): void
    {
        unset($this->bindings[$id], $this->instances[$id], $this->aliases[$id]);
    }

    public function initialized(string $id): bool
    {
        return $this->initialized && isset($this->instances[$id]);
    }

    public function getParameter(string $name): array|bool|string|int|float|null
    {
        if (!$this->hasParameter($name)) {
            throw new NotFoundException("Parameter '{$name}' not found");
        }
        return $this->parameters[$name];
    }

    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    public function setParameter(string $name, array|bool|string|int|float|\UnitEnum|null $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Lie une classe/interface à une implémentation
     */
    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared
        ];
    }

    /**
     * Lie une classe/interface comme singleton
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Enregistre une instance existante
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Crée un alias pour une classe
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Vérifie si une classe est enregistrée
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) ||
            isset($this->instances[$id]) ||
            isset($this->aliases[$id]) ||
            class_exists($id);
    }

    /**
     * Résout et instancie une classe avec ses dépendances
     */
    public function make(string $abstract, array $parameters = []): object
    {
        // Résolution des alias
        $abstract = $this->aliases[$abstract] ?? $abstract;

        // Instance déjà créée
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Binding personnalisé
        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];

            if ($concrete instanceof Closure) {
                $instance = $concrete($this, $parameters);
            } else {
                $instance = $this->build($concrete, $parameters);
            }

            // Singleton : on stocke l'instance
            if ($binding['shared']) {
                $this->instances[$abstract] = $instance;
            }

            return $instance;
        }

        // Auto-wiring : résolution automatique
        return $this->build($abstract, $parameters);
    }

    /**
     * Charge les providers dans le container
     */
    public function loadProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            if (is_string($provider)) {
                $provider = new $provider();
            }

            if ($provider instanceof ServiceProviderInterface) {
                $provider->register();
            } else {
                throw new ContainerException('Provider must implement ProviderInterface');
            }
            
        }
    }

    /**
     * Construit une instance avec résolution automatique des dépendances
     */
    private function build(string $concrete, array $parameters = []): object
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new NotFoundException("Class {$concrete} not found", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        // Pas de constructeur : instanciation simple
        if ($constructor === null) {
            return new $concrete();
        }

        // Résolution des paramètres du constructeur
        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Résout les dépendances d'une méthode
     */
    private function resolveDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // Paramètre fourni explicitement
            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }

            // Type hint disponible
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            // Valeur par défaut
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ContainerException(
                "Cannot resolve parameter '{$name}' for class"
            );
        }

        return $dependencies;
    }

    /**
     * Appelle une méthode avec injection de dépendances
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback);
            $callback = [$this->make($class), $method];
        }

        if (is_array($callback)) {
            [$object, $method] = $callback;
            if (is_string($object)) {
                $object = $this->make($object);
            }

            $reflector = new \ReflectionMethod($object, $method);
            $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);

            return $reflector->invokeArgs($object, $dependencies);
        }

        return $callback(...array_values($parameters));
    }

    /**
     * Flush toutes les instances et bindings
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
    }
}
