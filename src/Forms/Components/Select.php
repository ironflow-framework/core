<?php

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;

class Select extends Component
{
   protected array $options = [];
   protected bool $multiple = false;
   protected bool $required = false;

   public function __construct(string $name, string $label, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->options = $options['options'] ?? [];
      $this->multiple = $options['multiple'] ?? false;
      $this->required = $options['required'] ?? false;
   }

   public function render(): string
   {
      $attributes = [
         'name' => $this->name . ($this->multiple ? '[]' : ''),
         'id' => $this->name,
         'class' => $this->getOption('class', 'form-control'),
      ];

      if ($this->multiple) {
         $attributes['multiple'] = 'multiple';
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '<select ' . $this->buildAttributes($attributes) . '>';

      foreach ($this->options as $value => $label) {
         $selected = $this->value == $value ? ' selected' : '';
         $html .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
      }

      $html .= '</select>';

      if ($this->hasError()) {
         $html .= '<div class="error-message">' . implode(', ', $this->errors) . '</div>';
      }

      $html .= '</div>';

      return $html;
   }

   protected function buildAttributes(array $attributes): string
   {
      return implode(' ', array_map(
         fn($key, $value) => $value === true ? $key : "$key=\"$value\"",
         array_keys($attributes),
         $attributes
      ));
   }
}
