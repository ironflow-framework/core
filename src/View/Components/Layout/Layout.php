<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Layout;

use IronFlow\View\Components\Component;

abstract class Layout extends Component
{
   protected array $content = [];
   protected array $attributes = [];

   public function __construct(array $attributes = [])
   {
      parent::__construct([]);
      $this->attributes = $attributes;
   }

   public function addContent($content): self
   {
      $this->content[] = $content;
      return $this;
   }

   public function withAttributes(array $attributes): self
   {
      $this->attributes = array_merge($this->attributes, $attributes);
      return $this;
   }

   protected function renderAttributes(): string
   {
      $attrs = [];
      foreach ($this->attributes as $key => $value) {
         $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
      }
      return implode(' ', $attrs);
   }

   protected function renderContent(): string
   {
      return implode("\n", array_map(function ($content) {
         return is_string($content) ? $content : $content->render();
      }, $this->content));
   }

   abstract public function render(): string;
}
