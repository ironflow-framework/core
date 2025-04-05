<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Support\Collection as SupportCollection;

class Collection extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return 'collection';
   }

   protected static function getFacadeInstance(): object
   {
      return new SupportCollection();
   }
}