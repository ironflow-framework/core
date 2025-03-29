<?php

declare(strict_types=1);

namespace IronFlow\View;

/**
 * Classe de base pour tous les composants du framework
 */
abstract class Component
{
   protected array $attributes = [];
   protected array $props = [];
   protected array $slots = [];
   protected string $content = '';

   public function __construct(array $attributes = [])
   {
      $this->attributes = $attributes;
   }

   public function setContent(string $content): self
   {
      $this->content = $content;
      return $this;
   }

   protected function renderContent(): string
   {
      return $this->content;
   }

   protected function renderAttributes(): string
   {
      return implode(' ', $this->attributes);
   }

   public function withAttributes(array $attributes): self
   {
      $this->attributes = array_merge($this->attributes, $attributes);
      return $this;
   }

   public function withSlots(array $slots): self
   {
      $this->slots = array_merge($this->slots, $slots);
      return $this;
   }

   protected function getProp(string $key, $default = null)
   {
      return $this->props[$key] ?? $default;
   }

   protected function getSlot(string $key, $default = null)
   {
      return $this->slots[$key] ?? $default;
   }

   /**
    * Génère le rendu du composant
    *
    * @return string
    */
   abstract public function render(): string;

   /**
    * Convertit le composant en chaîne
    *
    * @return string
    */
   public function __toString(): string
   {
      return $this->render();
   }
}
