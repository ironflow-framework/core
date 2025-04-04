<?php

namespace IronFlow\Forms\Components;

class Textarea extends Component
{
   protected ?int $rows = null;
   protected ?int $cols = null;
   protected ?string $placeholder = null;
   protected bool $required = false;

   public function __construct(string $name, string $label, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->rows = $options['rows'] ?? null;
      $this->cols = $options['cols'] ?? null;
      $this->placeholder = $options['placeholder'] ?? null;
      $this->required = $options['required'] ?? false;
   }

   public function render(): string
   {
      $attributes = [
         'name' => $this->name,
         'id' => $this->name,
         'class' => $this->getOption('class', 'form-control'),
      ];

      if ($this->rows) {
         $attributes['rows'] = $this->rows;
      }

      if ($this->cols) {
         $attributes['cols'] = $this->cols;
      }

      if ($this->placeholder) {
         $attributes['placeholder'] = $this->placeholder;
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '<textarea ' . $this->buildAttributes($attributes) . '>' . ($this->value ?? '') . '</textarea>';

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
