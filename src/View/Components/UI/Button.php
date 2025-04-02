<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI;

use IronFlow\View\Component;

class Button extends Component
{
   protected string $type = 'button';
   protected string $variant = 'primary';
   protected string $size = 'md';
   protected bool $fullWidth = false;
   protected bool $disabled = false;
   protected ?string $icon = null;
   protected bool $iconOnly = false;
   protected bool $loading = false;

   public function __construct(?array $attributes = [])
   {
      parent::__construct($attributes);
   }

   public function type(string $type): self
   {
      $this->type = $type;
      return $this;
   }

   public function variant(string $variant): self
   {
      $this->variant = $variant;
      return $this;
   }

   public function size(string $size): self
   {
      $this->size = $size;
      return $this;
   }

   public function fullWidth(bool $fullWidth = true): self
   {
      $this->fullWidth = $fullWidth;
      return $this;
   }

   public function disabled(bool $disabled = true): self
   {
      $this->disabled = $disabled;
      return $this;
   }

   public function loading(bool $loading = true): self
   {
      $this->loading = $loading;
      return $this;
   }

   public function icon(?string $icon = null): self
   {
      $this->icon = $icon;
      return $this;
   }

   public function iconOnly(bool $iconOnly = true): self
   {
      $this->iconOnly = $iconOnly;
      return $this;
   }

   protected function getVariantClasses(): string
   {
      $variants = [
         'primary' => 'bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:ring-indigo-500',
         'secondary' => 'bg-white text-gray-700 rounded border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
         'success' => 'bg-green-600 text-white rounded hover:bg-green-700 focus:ring-green-500',
         'danger' => 'bg-red-600 text-white rounded hover:bg-red-700 focus:ring-red-500',
         'warning' => 'bg-yellow-600 text-white rounded hover:bg-yellow-700 focus:ring-yellow-500',
         'info' => 'bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-blue-500',
         'light' => 'bg-gray-100 text-gray-700 rounded hover:bg-gray-200 focus:ring-gray-500',
         'dark' => 'bg-gray-800 text-white rounded hover:bg-gray-900 focus:ring-gray-500',
      ];

      return $variants[$this->variant] ?? $variants['primary'];
   }

   protected function getSizeClasses(): string
   {
      $sizes = [
         'xs' => 'px-2.5 py-1.5 text-xs',
         'sm' => 'px-3 py-2 text-sm',
         'md' => 'px-4 py-2 text-sm',
         'lg' => 'px-4 py-2 text-base',
         'xl' => 'px-6 py-3 text-base',
      ];

      return $sizes[$this->size] ?? $sizes['md'];
   }

   public function render(): string
   {
      $classes = [
         'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
         $this->getVariantClasses(),
         $this->getSizeClasses(),
      ];

      if ($this->fullWidth) {
         array_push($classes,'w-full');
      }

      if ($this->disabled) {
         array_push($classes, 'opacity-50 cursor-not-allowed');
      }

      if ($this->disabled) {
        $this->attributes['disabled'] = 'disabled';
      }
      
      $content = [];

      if ($this->icon) {
         $iconClasses = $this->iconOnly ? '' : 'mr-2 -ml-1';
         $content[] = sprintf(
            '<span class="%s">%s</span>',
            $iconClasses,
            $this->icon
         );
      }

      if (!$this->iconOnly) {
         $content[] = $this->renderContent();
      }

      return sprintf(
         '<div class="form-action mb-4">
         <button type="%s" class="%s" %s>%s</button>
         </div>',
         $this->type,
         implode(' ', $classes),
         $this->renderAttributes(),
         implode('', $content)
      );
   }
}
