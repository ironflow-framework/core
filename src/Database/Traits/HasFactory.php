<?php

namespace IronFlow\Database\Traits;

use IronFlow\Database\Factories\Factory;
use IronFlow\Database\Model;

trait HasFactory
{
   /**
    * Crée une nouvelle instance de factory pour le modèle
    * 
    * @return Factory
    */
   public static function factory(): Factory
   {
      $factoryClass = static::getFactoryClass();

      if (!class_exists($factoryClass)) {
         throw new \RuntimeException("La classe de factory {$factoryClass} n'existe pas pour le modèle " . static::class);
      }

      return new $factoryClass();
   }

   /**
    * Détermine la classe de factory à utiliser
    * 
    * @return string
    */
   protected static function getFactoryClass(): string
   {
      $modelClass = static::class;
      $modelName = basename(str_replace('\\', '/', $modelClass));

      return "App\\Database\\Factories\\{$modelName}Factory";
   }
}
