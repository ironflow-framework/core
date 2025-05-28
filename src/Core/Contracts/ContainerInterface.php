<?php

declare(strict_types=1);

namespace IronFlow\Core\Contracts;

interface ContainerInterface
{
   /**
    * Enregistre une liaison dans le conteneur
    */
   public function bind(string $abstract, \Closure|string|null $concrete = null, bool $shared = false): void;

   /**
    * Enregistre une instance partagée dans le conteneur
    */
   public function singleton(string $abstract, \Closure|string|null $concrete = null): void;

   /**
    * Résout une instance du type demandé
    */
   public function make(string $abstract, array $parameters = []): mixed;

   /**
    * Vérifie si une liaison existe dans le conteneur
    */
   public function bound(string $abstract): bool;

   /**
    * Supprime une liaison du conteneur
    */
   public function unbind(string $abstract): void;

   /**
    * Vide le conteneur
    */
   public function flush(): void;

   /**
    * Vérifie si un service existe dans le conteneur
    */
   public function has(string $id): bool;

   /**
    * Récupère un service du conteneur
    */
   public function get(string $id): mixed;
   
}
