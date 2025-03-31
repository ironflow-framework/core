<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Filesystem as FilesystemClass;

class Filesystem
{
   public static function __callStatic(string $method, array $arguments)
   {
      return FilesystemClass::$method(...$arguments);
   }
}
