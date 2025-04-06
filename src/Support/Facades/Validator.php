<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Validation\Validator as ValidatorInstance;

class Validator extends Facade
{
   /**
    * Récupère l'instance du Validator
    */
   protected static function getFacadeInstance(): ValidatorInstance
   {
      return new ValidatorInstance([], []);
   }
   
}
