<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

abstract class Facade
{
   /**
    * Récupère l'instance de la façade
    */
   abstract protected static function getFacadeInstance(): object;

   /**
    * Gère les appels de méthodes statiques
    */
   public static function __callStatic(string $method, array $args): mixed
   {
      return static::getFacadeInstance()->$method(...$args);
   }
}
