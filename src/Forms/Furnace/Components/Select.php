<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Database\Collection;
use IronFlow\Forms\Furnace\Field;

class Select extends Field
{
   protected array|Collection $options = [];
   protected bool $multiple = false;
   protected ?string $placeholder = null;

   /**
    * Ajouter les options
    * @param array|\IronFlow\Database\Collection $options
    * @return Select
    */
   public function options(array|Collection $options): self
   {
      $this->options = $options;
      return $this;
   }

   public function multiple(bool $multiple = true): self
   {
      $this->multiple = $multiple;
      return $this;
   }

   public function placeholder(?string $placeholder): self
   {
      $this->placeholder = $placeholder;
      return $this;
   }

   public function render(): string
   {
      $baseAttributes = [
         'name' => $this->name . ($this->multiple ? '[]' : ''),
         'id' => $this->name,
         'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
      ];

      if ($this->multiple) {
         $baseAttributes['multiple'] = 'multiple';
      }

      if ($this->required) {
         $baseAttributes['required'] = 'required';
      }

      $this->attributes = array_merge($baseAttributes, $this->attributes);

      $options = '';

      if ($this->placeholder !== null) {
         $options .= sprintf(
            '<option value="" disabled %s>%s</option>',
            empty($this->value) ? 'selected' : '',
            htmlspecialchars($this->placeholder)
         );
      }

      foreach ($this->options as $value => $label) {
         $selected = $this->multiple
            ? in_array($value, (array)$this->value)
            : $value == $this->value;

         $options .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($value),
            $selected ? 'selected' : '',
            htmlspecialchars($label)
         );
      }

      return sprintf(
         '<select %s>%s</select>',
         $this->renderAttributes(),
         $options
      );
   }
}
