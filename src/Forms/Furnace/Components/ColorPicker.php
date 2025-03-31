<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\Field;

class ColorPicker extends Field
{
   protected array $presetColors = [];
   protected bool $showAlpha = false;

   public function presets(array $colors): self
   {
      $this->presetColors = $colors;
      return $this;
   }

   public function showAlpha(bool $show = true): self
   {
      $this->showAlpha = $show;
      return $this;
   }

   public function render(): string
   {
      $attributes = array_merge([
         'type' => 'color',
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'h-10 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
         'value' => $this->value,
      ], $this->attributes);

      if ($this->showAlpha) {
         $attributes['data-alpha'] = 'true';
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      $presetColorsHtml = '';
      if (!empty($this->presetColors)) {
         $presetColorsHtml = '<div class="mt-2 flex flex-wrap gap-2">';
         foreach ($this->presetColors as $color) {
            $presetColorsHtml .= sprintf(
               '<button type="button" class="h-8 w-8 rounded-full border-2 border-gray-300" style="background-color: %s" data-color="%s"></button>',
               htmlspecialchars($color),
               htmlspecialchars($color)
            );
         }
         $presetColorsHtml .= '</div>';
      }

      return sprintf(
         '<div class="form-group mb-4">
                %s
                <div class="flex items-center gap-4">
                    <input %s>
                    <div class="h-10 w-10 rounded-md border-2 border-gray-300" style="background-color: %s"></div>
                </div>
                %s
                %s
            </div>',
         $this->renderLabel(),
         $this->renderAttributes(),
         htmlspecialchars($this->value ?: '#000000'),
         $presetColorsHtml,
         $this->renderError()
      );
   }
}
