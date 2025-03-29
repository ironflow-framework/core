<?php

declare(strict_types=1);

namespace IronFlow\Foundation;

use Closure;
use Psr\Container\ContainerInterface;
use IronFlow\Foundation\Exceptions\ContainerException;
use IronFlow\Foundation\Exceptions\NotFoundException;

/**
 * Container d'injection de dépendances compatible PSR-11
 * 
 * Cette classe fournit un container d'injection de dépendances permettant
 * d'enregistrer et de résoudre des services dans l'application.
 */
class Container implements ContainerInterface
{
   /**
    * Liste des bindings du container
    *
    * @var array
    */
   private array $bindings = [];

   /**
    * Liste des instances déjà résolues
    *
    * @var array
    */
   private array $instances = [];

   /**
    * Enregistre un service comme singleton
    *
    * Un singleton est une instance qui ne sera créée qu'une seule fois
    * et réutilisée lors des appels suivants.
    *
    * @param string $name Nom du service
    * @param Closure $callback Callback qui crée le service
    * @return void
    */
   public function singleton(string $name, Closure $callback): void
   {
      $this->bindings[$name] = [
         'concrete' => $callback,
         'shared' => true
      ];
   }

   /**
    * Enregistre un service standard
    *
    * Une nouvelle instance sera créée à chaque appel.
    *
    * @param string $name Nom du service
    * @param Closure $callback Callback qui crée le service
    * @return void
    */
   public function bind(string $name, Closure $callback): void
   {
      $this->bindings[$name] = [
         'concrete' => $callback,
         'shared' => false
      ];
   }

   /**
    * Enregistre une instance déjà créée dans le container
    *
    * @param string $name Nom du service
    * @param mixed $instance Instance du service
    * @return void
    */
   public function instance(string $name, mixed $instance): void
   {
      $this->instances[$name] = $instance;
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
    * Supprime un service du container
    *
    * @param string $name Nom du service
    * @return void
    */
   public function remove(string $name): void
   {
      unset($this->bindings[$name], $this->instances[$name]);
   }

   /**
    * Vide le container de tous ses services
    *
    * @return void
    */
   public function clear(): void
   {
      $this->bindings = [];
      $this->instances = [];
   }

   /**
    * Compte le nombre total de services enregistrés
    *
    * @return int
    */
   public function count(): int
   {
      return count($this->bindings) + count($this->instances);
   }

   /**
    * Récupère la liste des bindings
    *
    * @return array
    */
   public function getBindings(): array
   {
      return $this->bindings;
   }

   /**
    * Récupère la liste des instances
    *
    * @return array
    */
   public function getInstances(): array
   {
      return $this->instances;
   }
}
