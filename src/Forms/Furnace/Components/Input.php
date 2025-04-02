<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\Field;

class Input extends Field
{
   protected string $type = 'text';
   protected string $placeholder = '';

   public function type(string $type): self
   {
      $this->type = $type;
      return $this;
   }

   public function placeholder(string $placeholder): self
   {
      $this->placeholder = $placeholder;
      return $this;
   }

   public function render(): string
   {
      $baseAttributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'value' => $this->value,
         'class' => 'mt-1 block w-full rounded-md py-3 px-2 text-gray-700 text-md font-medium border-gray-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
         'placeholder' => $this->placeholder,
      ];

      if ($this->required) {
         $baseAttributes['required'] = 'required';
      }

      $this->attributes = array_merge($baseAttributes, $this->attributes);

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
