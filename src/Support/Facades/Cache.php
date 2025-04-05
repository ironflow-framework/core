<?php

namespace IronFlow\Support\Facades;

use IronFlow\Cache\Hammer\HammerManager;

class Cache extends Facade
{
    protected static function getFacadeAccessor(): string
   {
    return HammerManager::class;
   }
   protected static function getFacadeInstance(): object
   {
      return HammerManager::getInstance();
   }
}
