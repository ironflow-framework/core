<?php

namespace IronFlow\Forms\Components;

class Input extends Component
{
   protected string $type = 'text';
   protected ?string $placeholder = null;
   protected bool $required = false;
   protected ?string $pattern = null;
   protected ?string $min = null;
   protected ?string $max = null;

   public function __construct(string $name, string $label, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->type = $options['type'] ?? 'text';
      $this->placeholder = $options['placeholder'] ?? null;
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->min = $options['min'] ?? null;
      $this->max = $options['max'] ?? null;
   }

   public function render(): string
   {
      $attributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'class' => $this->getOption('class', 'form-control'),
      ];

      if ($this->value !== null) {
         $attributes['value'] = $this->value;
      }

      if ($this->placeholder) {
         $attributes['placeholder'] = $this->placeholder;
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      if ($this->pattern) {
         $attributes['pattern'] = $this->pattern;
      }

      if ($this->min) {
         $attributes['min'] = $this->min;
      }

      if ($this->max) {
         $attributes['max'] = $this->max;
      }

      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '<input ' . $this->buildAttributes($attributes) . '>';

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
