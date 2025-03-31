<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

use IronFlow\Forms\Furnace\ChoiceField;

class Checkbox extends ChoiceField
{
   protected string $type = 'checkbox';

   protected function renderOption(string $value, string $label, bool $checked): string
   {
      $id = sprintf('%s_%s', $this->name, $value);
      $name = sprintf('%s[]', $this->name);

      return sprintf(
         '<div class="flex items-center">
                <input type="%s" 
                    id="%s" 
                    name="%s" 
                    value="%s" 
                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                    %s
                    %s>
                <label for="%s" class="ml-2 block text-sm text-gray-900">
                    %s
                </label>
            </div>',
         $this->type,
         $id,
         $name,
         htmlspecialchars($value),
         $checked ? 'checked' : '',
         $this->required ? 'required' : '',
         $id,
         htmlspecialchars($label)
      );
   }
}
