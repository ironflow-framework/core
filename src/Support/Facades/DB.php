<?php

namespace IronFlow\Support\Facades;

use IronFlow\Database\Connection as Database;

class DB
{
   public static function __callStatic(string $method, array $arguments)
   {
      return Database::$method(...$arguments);
   }
}
