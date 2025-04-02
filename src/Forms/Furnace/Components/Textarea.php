<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\Field;

class Textarea extends Field
{
   protected int $rows = 3;
   protected string $placeholder = '';

   public function rows(int $rows): self
   {
      $this->rows = $rows;
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
         'name' => $this->name,
         'id' => $this->name,
         'rows' => $this->rows,
         'class' => 'mt-1 block w-full rounded-md py-3 px-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm resize-none',
         'placeholder' => $this->placeholder,
      ];

      if ($this->required) {
         $baseAttributes['required'] = 'required';
      }

      $this->attributes = array_merge($baseAttributes, $this->attributes);

      return sprintf(
         '<div class="form-group mb-4">
         %s
         <textarea %s>%s</textarea>
          %s
          </div>',
         $this->renderLabel(),
         $this->renderAttributes(),
         htmlspecialchars($this->value),
         $this->renderError()
      );
   }
}
