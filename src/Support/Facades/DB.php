<?php

namespace IronFlow\Support\Facades;

use IronFlow\Database\Connection as Database;

class DB extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return Database::class;
   }
}
