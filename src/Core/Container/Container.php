<?php

declare(strict_types=1);

namespace IronFlow\Core\Container;

use Closure;
use InvalidArgumentException;
use IronFlow\Core\Contracts\ContainerInterface;
use IronFlow\Core\Exceptions\NotFoundException;
use IronFlow\Routing\RouterInterface;
use IronFlow\Routing\Router;
use ReflectionClass;
use ReflectionParameter;
use stdClass;

class Container implements ContainerInterface
{
   /**
    * Liaisons enregistrées
    */
   private array $bindings = [];

   /**
    * Instances partagées
    */
   private array $instances = [];

   /**
    * Constructeur
    */
   public function __construct()
   {
      // Liaisons par défaut
      $this->bind(\IronFlow\View\ViewInterface::class, function ($container) {
         return new \IronFlow\View\TwigView(base_path('resources/views'));
      });
   }

   /**
    * Enregistre une liaison dans le conteneur
    */
   public function bind(string $abstract, Closure|string|stdClass|null $concrete = null, bool $shared = false): void
   {
      if (is_null($concrete)) {
         $concrete = $abstract;
      }

      if (!$concrete instanceof Closure) {
         $concrete = $this->getClosure($abstract, $concrete);
      }

      $this->bindings[$abstract] = compact('concrete', 'shared');
   }

   /**
    * Enregistre une instance partagée dans le conteneur
    */
   public function singleton(string $abstract, Closure|string|null $concrete = null): void
   {
      $this->bind($abstract, $concrete, true);
   }

   /**
    * Résout une instance du type demandé
    */
   public function make(string $abstract, array $parameters = []): mixed
   {
      if (isset($this->instances[$abstract])) {
         return $this->instances[$abstract];
      }

      $concrete = $this->getConcrete($abstract);

      if ($this->isBuildable($concrete, $abstract)) {
         $object = $this->build($concrete, $parameters);
      } else {
         $object = $this->make($concrete, $parameters);
      }

      if (isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'] === true) {
         $this->instances[$abstract] = $object;
      }

      return $object;
   }

   /**
    * Vérifie si une liaison existe dans le conteneur
    */
   public function bound(string $abstract): bool
   {
      return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
   }

   /**
    * Supprime une liaison du conteneur
    */
   public function unbind(string $abstract): void
   {
      unset($this->bindings[$abstract], $this->instances[$abstract]);
   }

   /**
    * Vérifie si un service existe dans le container
    *
    * @param string $id Identifiant du service
    * @return bool
    */
   public function has(string $id): bool
   {
      return isset($this->bindings[$id]) || isset($this->instances[$id]);
   }

   /**
    * Vide le conteneur
    */
   public function flush(): void
   {
      $this->bindings = [];
      $this->instances = [];
   }

   /**
    * Obtient la closure pour la liaison
    */
   protected function getClosure(string $abstract, string $concrete): Closure
   {
      return function ($container) use ($abstract, $concrete) {
         if ($abstract === $concrete) {
            return $container->build($concrete);
         }
         return $container->make($concrete);
      };
   }

   /**
    * Obtient le concret pour l'abstrait
    */
   protected function getConcrete(string $abstract): string|Closure
   {
      if (!isset($this->bindings[$abstract])) {
         return $abstract;
      }

      return $this->bindings[$abstract]['concrete'];
   }

   /**
    * Récupère un service du container
    *
    * @param string $id Identifiant du service
    * @return mixed Instance du service
    * @throws NotFoundException Si le service n'existe pas
    */
   public function get(string $id): mixed
   {
      if (isset($this->instances[$id])) {
         return $this->instances[$id];
      }

      if (!$this->has($id)) {
         throw new NotFoundException("Service '{$id}' not found in container");
      }

      $binding = $this->bindings[$id];
      $concrete = $binding['concrete']($this);

      if ($binding['shared']) {
         $this->instances[$id] = $concrete;
      }

      return $concrete;
   }

   /**
    * Vérifie si le concret est constructible
    */
   protected function isBuildable(Closure|string $concrete, string $abstract): bool
   {
      return $concrete === $abstract || $concrete instanceof Closure;
   }

   /**
    * Construit une instance du type donné
    */
   protected function build(Closure|string $concrete, array $parameters = []): mixed
   {
      if ($concrete instanceof Closure) {
         return $concrete($this, $parameters);
      }

      $reflector = new ReflectionClass($concrete);

      if (!$reflector->isInstantiable()) {
         throw new InvalidArgumentException("Target [$concrete] is not instantiable.");
      }

      $constructor = $reflector->getConstructor();

      if (is_null($constructor)) {
         return new $concrete;
      }

      $dependencies = $constructor->getParameters();
      $instances = $this->resolveDependencies($dependencies);

      return $reflector->newInstanceArgs($instances);
   }

   /**
    * Résout les dépendances pour les paramètres donnés
    */
   protected function resolveDependencies(array $dependencies): array
   {
      $results = [];

      foreach ($dependencies as $dependency) {
         $results[] = $this->resolveDependency($dependency);
      }

      return $results;
   }

   /**
    * Résout une dépendance individuelle
    */
   protected function resolveDependency(ReflectionParameter $parameter): mixed
   {
      $type = $parameter->getType();

      if ($type && !$type->isBuiltin()) {
         $className = $type->getName();
         if ($className === RouterInterface::class) {
            return $this->make(Router::class);
         }
         return $this->make($className);
      }

      if ($parameter->isDefaultValueAvailable()) {
         return $parameter->getDefaultValue();
      }

      throw new InvalidArgumentException("Unresolvable dependency resolving [$parameter]");
   }
}
