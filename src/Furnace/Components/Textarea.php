<?php

declare(strict_types=1);

namespace IronFlow\Furnace\Components;

use IronFlow\Furnace\Field;

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
         'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
         'placeholder' => $this->placeholder,
      ];

      if ($this->required) {
         $baseAttributes['required'] = 'required';
      }

      $this->attributes = array_merge($baseAttributes, $this->attributes);

      return sprintf(
         '<textarea %s>%s</textarea>',
         $this->renderAttributes(),
         htmlspecialchars($this->value)
      );
   }
}
