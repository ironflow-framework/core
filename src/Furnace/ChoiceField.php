<?php

declare(strict_types=1);

namespace IronFlow\Forms;

abstract class ChoiceField extends Field
{
   protected array $options = [];
   protected bool $inline = false;
   protected string $type = '';

   public function options(array $options): self
   {
      $this->options = $options;
      return $this;
   }

   public function inline(bool $inline = true): self
   {
      $this->inline = $inline;
      return $this;
   }

   protected function renderLabel(): string
   {
      return sprintf(
         '<label class="block text-sm font-medium text-gray-700 mb-2">%s%s</label>',
         $this->label,
         $this->required ? ' <span class="text-red-500">*</span>' : ''
      );
   }

   protected function renderOptions(): string
   {
      $options = [];
      $value = $this->value;
      $isArray = is_array($value);

      foreach ($this->options as $key => $label) {
         $checked = $isArray
            ? in_array($key, $label)
            : $key == $value;

         $options[] = $this->renderOption($key, $label, $checked);
      }

      $class = $this->inline ? 'flex space-x-4' : 'space-y-2';
      return sprintf('<div class="%s">%s</div>', $class, implode("\n", $options));
   }

   abstract protected function renderOption(string $value, string $label, bool $checked): string;

   public function render(): string
   {
      return sprintf(
         '<div class="form-group mb-4">%s%s%s</div>',
         $this->renderLabel(),
         $this->renderOptions(),
         $this->renderError()
      );
   }
}
