<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\Field;

class DatePicker extends Field
{
   protected ?string $min = null;
   protected ?string $max = null;
   protected string $format = 'Y-m-d';
   protected bool $showTime = false;

   public function min(string $date): self
   {
      $this->min = $date;
      return $this;
   }

   public function max(string $date): self
   {
      $this->max = $date;
      return $this;
   }

   public function format(string $format): self
   {
      $this->format = $format;
      return $this;
   }

   public function showTime(bool $show = true): self
   {
      $this->showTime = $show;
      return $this;
   }

   public function render(): string
   {
      $attributes = array_merge([
         'type' => $this->showTime ? 'datetime-local' : 'date',
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
         'value' => $this->value,
      ], $this->attributes);

      if ($this->min) {
         $attributes['min'] = $this->min;
      }

      if ($this->max) {
         $attributes['max'] = $this->max;
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
