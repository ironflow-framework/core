<?php

namespace IronFlow\Support\Facades;

use IronFlow\View\TwigView;

class View
{
   public static function __callStatic(string $method, array $arguments)
   {
      return TwigView::$method(...$arguments);
   }
}
