<?php

declare(strict_types=1);

namespace IronFlow\Components;

class ComponentManager
{
   public static function render(string $component, array $props = []): string
   {
      $class = "App\\Components\\" . $component . "Component";

      if (!class_exists($class)) {
         throw new \Exception("Le composant {$component} est introuvable.");
      }

      return (new $class($props))->render();
   }
}
