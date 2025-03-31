<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\Field;

class File extends Field
{
   protected array $acceptedTypes = [];
   protected int $maxSize = 0;
   protected bool $multiple = false;

   public function accept(string|array $types): self
   {
      $this->acceptedTypes = is_array($types) ? $types : [$types];
      return $this;
   }

   public function maxSize(int $size): self
   {
      $this->maxSize = $size;
      return $this;
   }

   public function multiple(bool $multiple = true): self
   {
      $this->multiple = $multiple;
      return $this;
   }

   public function render(): string
   {
      $attributes = array_merge([
         'type' => 'file',
         'name' => $this->name . ($this->multiple ? '[]' : ''),
         'id' => $this->name,
         'class' => 'block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-indigo-50 file:text-indigo-700
                hover:file:bg-indigo-100
                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
      ], $this->attributes);

      if (!empty($this->acceptedTypes)) {
         $attributes['accept'] = implode(',', $this->acceptedTypes);
      }

      if ($this->maxSize > 0) {
         $attributes['data-max-size'] = $this->maxSize;
      }

      if ($this->multiple) {
         $attributes['multiple'] = 'multiple';
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      return sprintf(
         '<div class="form-group mb-4">
                %s
                <input %s>
                %s
            </div>',
         $this->renderLabel(),
         $this->renderAttributes(),
         $this->renderError()
      );
   }
}
