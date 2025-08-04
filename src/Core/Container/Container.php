<?php

declare(strict_types=1);

// ===== CONTAINER AMÉLIORÉ =====

namespace IronFlow\Core\Container;

use IronFlow\Core\Exception\Container\ContainerException;
use IronFlow\Core\Exception\Container\NotFoundException;
use IronFlow\Core\Container\Concerns\ContainerAwareInterface;
use ReflectionClass;
use ReflectionParameter;
use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container d'injection de dépendances avancé
 * 
 * Container DI avec auto-wiring, singletons, factories,
 * résolution contextuelle et gestion des cycles de dépendances.
 */
class Container implements ContainerInterface, PsrContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];
    private array $parameters = [];
    private array $tags = [];
    private array $contextual = [];
    private array $buildStack = [];
    private array $factories = [];
    private bool $booted = false;

    public function __construct()
    {
        $this->instance(self::class, $this);
        $this->instance(ContainerInterface::class, $this);
        $this->instance(PsrContainerInterface::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        try {
            return $this->make($id);
        } catch (NotFoundException $e) {
            if ($invalidBehavior === self::EXCEPTION_ON_INVALID_REFERENCE) {
                throw $e;
            }
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) ||
               isset($this->instances[$id]) ||
               isset($this->aliases[$id]) ||
               $this->isAutoWirable($id);
    }

    /**
     * Enregistre un binding avec configuration avancée
     */
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): static
    {
        $this->dropStaleInstances($abstract);

        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
            'tags' => [],
            'when' => []
        ];

        return $this;
    }

    /**
     * Enregistre un singleton
     */
    public function singleton(string $abstract, mixed $concrete = null): static
    {
        return $this->bind($abstract, $concrete, true);
    }

    /**
     * Enregistre une factory
     */
    public function factory(string $abstract, Closure $factory): static
    {
        $this->factories[$abstract] = $factory;
        return $this;
    }

    /**
     * Enregistre une instance existante
     */
    public function instance(string $abstract, object $instance): static
    {
        $this->removeAbstractAlias($abstract);
        unset($this->bindings[$abstract]);
        $this->instances[$abstract] = $instance;

        return $this;
    }

    /**
     * Crée un alias
     */
    public function alias(string $abstract, string $alias): static
    {
        if ($alias === $abstract) {
            throw new ContainerException("Alias cannot be the same as the abstract [{$abstract}]");
        }

        $this->aliases[$alias] = $abstract;
        return $this;
    }

    /**
     * Tag des services pour une récupération groupée
     */
    public function tag(array|string $abstracts, array|string $tags): static
    {
        $tags = (array) $tags;
        
        foreach ((array) $abstracts as $abstract) {
            if (isset($this->bindings[$abstract])) {
                $this->bindings[$abstract]['tags'] = array_merge(
                    $this->bindings[$abstract]['tags'],
                    $tags
                );
            }
            
            foreach ($tags as $tag) {
                $this->tags[$tag][] = $abstract;
                $this->tags[$tag] = array_unique($this->tags[$tag]);
            }
        }

        return $this;
    }

    /**
     * Récupère tous les services avec un tag donné
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        return array_map(fn($abstract) => $this->make($abstract), $this->tags[$tag]);
    }

    /**
     * Binding contextuel
     */
    public function when(string $concrete): ContextualBindingBuilder
    {
        return new ContextualBindingBuilder($this, $concrete);
    }

    /**
     * Ajoute un binding contextuel
     */
    public function addContextualBinding(string $concrete, string $abstract, mixed $implementation): void
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Résout et instancie une classe
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Résolution principale
     */
    protected function resolve(string $abstract, array $parameters = [], bool $raiseEvents = true): mixed
    {
        $abstract = $this->getAlias($abstract);

        // Vérification des cycles de dépendances
        if (in_array($abstract, $this->buildStack)) {
            throw new ContainerException("Circular dependency detected: " . implode(' -> ', $this->buildStack) . " -> {$abstract}");
        }

        // Instance déjà créée
        if (isset($this->instances[$abstract]) && empty($parameters)) {
            return $this->instances[$abstract];
        }

        $this->buildStack[] = $abstract;

        try {
            // Factory personnalisée
            if (isset($this->factories[$abstract])) {
                $object = $this->factories[$abstract]($this, $parameters);
            }
            // Binding personnalisé
            elseif (isset($this->bindings[$abstract])) {
                $object = $this->build($this->getConcrete($abstract), $parameters);
            }
            // Auto-wiring
            else {
                $object = $this->build($abstract, $parameters);
            }

            // Injection des propriétés pour les classes ContainerAware
            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($this);
            }

            // Singleton : stockage de l'instance
            if ($this->isShared($abstract) && empty($parameters)) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        } finally {
            array_pop($this->buildStack);
        }
    }

    /**
     * Construction d'une instance avec résolution des dépendances
     */
    protected function build(mixed $concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (!is_string($concrete)) {
            throw new ContainerException('Invalid concrete type provided');
        }

        $reflector = $this->getReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $this->resolveDependencies(
            $constructor->getParameters(),
            $parameters,
            $concrete
        );

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Résolution des dépendances avec binding contextuel
     */
    protected function resolveDependencies(array $dependencies, array $parameters, string $concrete): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if ($this->hasParameterOverride($dependency, $parameters)) {
                $results[] = $parameters[$dependency->getName()];
                continue;
            }

            $result = $this->resolveDependency($dependency, $concrete);
            
            if (is_null($result) && $dependency->isDefaultValueAvailable()) {
                $result = $dependency->getDefaultValue();
            }

            if (is_null($result) && !$dependency->allowsNull()) {
                $this->unresolvablePrimitive($dependency, $concrete);
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Résout une dépendance individuelle
     */
    protected function resolveDependency(ReflectionParameter $parameter, string $concrete): mixed
    {
        // Binding contextuel
        if ($this->hasContextualBinding($parameter, $concrete)) {
            return $this->make($this->getContextualConcrete($parameter, $concrete));
        }

        $type = $parameter->getType();
        
        if (!$type || $type->isBuiltin()) {
            return null;
        }

        $className = $type->getName();
        
        // Variadic parameter
        if ($parameter->isVariadic()) {
            return $this->resolveVariadicClass($className);
        }

        return $this->make($className);
    }

    /**
     * Appel de méthode avec injection de dépendances
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        return BoundMethod::call($this, $callback, $parameters);
    }

    /**
     * Vérifie si une classe est auto-wirable
     */
    protected function isAutoWirable(string $id): bool
    {
        if (!class_exists($id) && !interface_exists($id)) {
            return false;
        }

        $reflector = new ReflectionClass($id);
        return $reflector->isInstantiable();
    }

    /**
     * Obtient l'alias réel d'un abstract
     */
    protected function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Obtient le concrete d'un binding
     */
    protected function getConcrete(string $abstract): mixed
    {
        return $this->bindings[$abstract]['concrete'] ?? $abstract;
    }

    /**
     * Vérifie si un service est partagé (singleton)
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
               (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']);
    }

    /**
     * Vérifie s'il y a un binding contextuel
     */
    protected function hasContextualBinding(ReflectionParameter $parameter, string $concrete): bool
    {
        $type = $parameter->getType();
        return $type && isset($this->contextual[$concrete][$type->getName()]);
    }

    /**
     * Obtient le concrete contextuel
     */
    protected function getContextualConcrete(ReflectionParameter $parameter, string $concrete): mixed
    {
        $type = $parameter->getType();
        return $this->contextual[$concrete][$type->getName()];
    }

    // Méthodes utilitaires et gestion d'erreurs...
    
    protected function getReflectionClass(string $concrete): ReflectionClass
    {
        try {
            return new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new NotFoundException("Target class [{$concrete}] does not exist.", 0, $e);
        }
    }

    protected function notInstantiable(string $concrete): void
    {
        if (!empty($this->buildStack)) {
            $previous = implode(', ', $this->buildStack);
            throw new ContainerException("Target [{$concrete}] is not instantiable while building [{$previous}].");
        }

        throw new ContainerException("Target [{$concrete}] is not instantiable.");
    }

    protected function hasParameterOverride(ReflectionParameter $dependency, array $parameters): bool
    {
        return array_key_exists($dependency->getName(), $parameters);
    }

    protected function unresolvablePrimitive(ReflectionParameter $parameter, string $concrete): void
    {
        throw new ContainerException("Unresolvable dependency resolving [{$parameter}] in class {$concrete}");
    }

    protected function resolveVariadicClass(string $className): array
    {
        return $this->tagged($className);
    }

    protected function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    protected function removeAbstractAlias(string $searched): void
    {
        if (!isset($this->aliases[$searched])) {
            return;
        }

        foreach ($this->aliases as $alias => $abstract) {
            if ($abstract === $searched) {
                unset($this->aliases[$alias]);
            }
        }
    }

    /**
     * Flush le container
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
        $this->tags = [];
        $this->contextual = [];
        $this->factories = [];
        $this->buildStack = [];
    }

    // Implémentation des méthodes Symfony manquantes
    public function set(string $id, mixed $service): void
    {
        $this->instance($id, $service);
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

    public function initialized(string $id): bool
    {
        return isset($this->instances[$id]);
    }
}
