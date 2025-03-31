<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Core\Logger\LogManager;

class Log
{
   protected static $instance;

   public static function getInstance()
   {
      if (!isset(self::$instance)) {
         self::$instance = new LogManager();
      }
      return self::$instance;
   }

   public static function __callStatic($method, $args)
   {
      return self::getInstance()->$method(...$args);
   }
}
