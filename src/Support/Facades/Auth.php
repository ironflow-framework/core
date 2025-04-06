<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Auth\AuthManager;

class Auth extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return AuthManager::class;
   }

   protected static function getFacadeInstance(): object
   {
      return AuthManager::getInstance();
   }

}
