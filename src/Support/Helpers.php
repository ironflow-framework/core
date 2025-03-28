<?php

declare(strict_types=1);

namespace IronFlow\Support;

use IronFlow\Http\Request;

class Helpers
{
   public static function classUsesRecursive($class): array
   {
      if (is_object($class)) {
         $class = get_class($class);
      }

      $results = [];

      foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
         $results += trait_uses_recursive($class);
      }

      return array_unique($results);
   }

   public static function traitUsesRecursive($trait): array
   {
      $traits = class_uses($trait);

      foreach ($traits as $trait) {
         $traits += trait_uses_recursive($trait);
      }
      return $traits;
   }
}
