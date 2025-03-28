<?php

declare(strict_types=1);

namespace IronFlow\Furnace\Components;

use IronFlow\Furnace\ChoiceField;

class Radio extends ChoiceField
{
   protected string $type = 'radio';

   protected function renderOption(string $value, string $label, bool $checked): string
   {
      $id = sprintf('%s_%s', $this->name, $value);

      return sprintf(
         '<div class="flex items-center">
                <input type="%s" 
                    id="%s" 
                    name="%s" 
                    value="%s" 
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    %s
                    %s>
                <label for="%s" class="ml-2 block text-sm text-gray-900">
                    %s
                </label>
            </div>',
         $this->type,
         $id,
         $this->name,
         htmlspecialchars($value),
         $checked ? 'checked' : '',
         $this->required ? 'required' : '',
         $id,
         htmlspecialchars($label)
      );
   }
}
