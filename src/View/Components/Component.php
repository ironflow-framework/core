<?php

declare(strict_types=1);

namespace IronFlow\View\Components;

abstract class Component
{
   protected array $props = [];
   protected array $slots = [];
   protected string $template = '';

   public function __construct(array $props = [])
   {
      $this->props = $props;
   }

   public function render(): string
   {
      if (empty($this->template)) {
         throw new \RuntimeException('Template not defined for component');
      }

      return $this->template;
   }

   public function withProps(array $props): self
   {
      $this->props = array_merge($this->props, $props);
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
}
