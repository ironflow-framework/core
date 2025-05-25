<?php

namespace IronFlow\Support\Facades;

use IronFlow\Cache\CacheManager;

class Cache extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return CacheManager::class;
   }
   protected static function getFacadeInstance(): object
   {
      return CacheManager::getInstance();
   }
}
