<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace;

use IronFlow\View\Component;

abstract class Field extends Component
{
   protected string $name;
   protected string $label;
   protected string $value = '';
   protected array $attributes = [];
   protected bool $required = false;
   protected string $error = '';

   public function __construct(string $name, string $label = '')
   {
      parent::__construct([]);
      $this->name = $name;
      $this->label = $label ?: ucfirst($name);
   }

   public function getName(): string
   {
      return $this->name;
   }

   public function getLabel(): string
   {
      return $this->label;
   }

   public function value(string $value): self
   {
      $this->value = $value;
      return $this;
   }

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function withError(string $error): self
   {
      $this->error = $error;
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
         if (is_string($value))
         {
            $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
         } else {
            $attrs[] = sprintf('%s="%s"', $key, $value);
         }
      }
      return implode(' ', $attrs);
   }

   protected function renderLabel(): string
   {
      return sprintf(
         '<label for="%s" class="block text-sm font-medium text-gray-700">%s%s</label>',
         $this->name,
         $this->label,
         $this->required ? ' <span class="text-red-500">*</span>' : ''
      );
   }

   protected function renderError(): string
   {
      if (empty($this->error)) {
         return '';
      }

      return sprintf(
         '<p class="mt-1 text-sm text-red-600">%s</p>',
         htmlspecialchars($this->error)
      );
   }

   abstract public function render(): string;
}
