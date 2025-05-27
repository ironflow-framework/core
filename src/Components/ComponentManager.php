<?php

declare(strict_types=1);

namespace IronFlow\Components;

use IronFlow\Components\Exceptions\ComponentException;
use ReflectionClass;

class ComponentManager
{
   protected static array $registeredComponents = [];
   protected static array $componentAliases = [];

   /**
    * Rend un composant avec gestion avancée des props
    */   public static function render(string $component, array $props = []): string
   {
      $componentClass = static::resolveComponent($component);

      if (!class_exists($componentClass)) {
         throw new ComponentException("Le composant '{$component}' est introuvable.");
      }

      // Assurons-nous que les props sont bien un tableau unique
      $normalizedProps = is_array($props) ? $props : [$props];

      return (new $componentClass($normalizedProps))->render();
   }

   /**
    * Enregistre un composant avec un alias
    */
   public static function register(string $alias, string $componentClass): void
   {
      if (!class_exists($componentClass)) {
         throw new ComponentException("La classe '{$componentClass}' n'existe pas.");
      }

      static::$registeredComponents[$alias] = $componentClass;
   }

   /**
    * Crée un alias pour un composant
    */
   public static function alias(string $alias, string $component): void
   {
      static::$componentAliases[$alias] = $component;
   }

   /**
    * Résout le nom du composant vers sa classe
    */
   protected static function resolveComponent(string $component): string
   {
      // Vérifier les alias
      if (isset(static::$componentAliases[$component])) {
         $component = static::$componentAliases[$component];
      }

      // Vérifier les composants enregistrés
      if (isset(static::$registeredComponents[$component])) {
         return static::$registeredComponents[$component];
      }

      // Convention par défaut
      return "App\\Components\\" . ucfirst($component) . "Component";
   }

   /**
    * Rend plusieurs composants en série
    */
   public static function renderAll(array $components): string
   {
      $output = '';
      foreach ($components as $component => $props) {
         if (is_numeric($component)) {
            // Si pas de props spécifiées
            $output .= static::render($props);
         } else {
            // Avec props
            $output .= static::render($component, ...(is_array($props) ? $props : [$props]));
         }
      }
      return $output;
   }

   /**
    * Vérifie si un composant existe
    */
   public static function exists(string $component): bool
   {
      $componentClass = static::resolveComponent($component);
      return class_exists($componentClass);
   }

   /**
    * Obtient la liste de tous les composants enregistrés
    */
   public static function getRegistered(): array
   {
      return static::$registeredComponents;
   }

   /**
    * Introspection d'un composant
    */
   public static function inspect(string $component): array
   {
      $componentClass = static::resolveComponent($component);

      if (!class_exists($componentClass)) {
         throw new ComponentException("Le composant '{$component}' est introuvable.");
      }

      $reflection = new ReflectionClass($componentClass);
      $instance = $reflection->newInstanceWithoutConstructor();

      return [
         'class' => $componentClass,
         'file' => $reflection->getFileName(),
         'methods' => array_map(fn($method) => $method->getName(), $reflection->getMethods()),
         'properties' => array_map(fn($prop) => $prop->getName(), $reflection->getProperties()),
         'defaults' => method_exists($instance, 'defaults') ? $instance->defaults() : [],
         'rules' => method_exists($instance, 'rules') ? $instance->rules() : []
      ];
   }
}
