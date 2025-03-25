<?php

namespace IronFlow\Database\Factories;

use Exception;

trait HasFactory
{
   public static function factory(?int $nbFactories)
   {
      $factoryClass = 'Database\\Factories\\' . class_basename(static::class) . 'Factory';

      if (!class_exists($factoryClass)) {
         throw new Exception("Factory class $factoryClass does not exist.");
      }

      $instance = (isset($nbFactories) && $nbFactories > 2) ? new $factoryClass(static::class)->count($nbFactories) : new $factoryClass(static::class);

      return $instance;
   }

   /**
    * Retourne la classe associée au modèle.
    *
    * @return string
    */
   protected function getModelClass(): string
   {
      return static::class;
   }
}
