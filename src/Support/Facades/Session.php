<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

class Session extends Facade
{

   protected static function getFacadeAccessor(): string
   {
      return 'Session';
   }

   public static function getInstance(){
      return new \IronFlow\Session\Session();
   }
}
