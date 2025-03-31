<?php

namespace IronFlow\Support\Facades;

use IronFlow\Routing\Router;

class Route
{
   /**
    * Gère les appels statiques et les redirige vers la classe utilitaire
    *
    * @param string $method
    * @param array $arguments
    * @return mixed
    */
   public static function __callStatic(string $method, array $arguments)
   {
       return Router::$method(...$arguments);
   }
}
