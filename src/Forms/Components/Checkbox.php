<?php

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;

class Checkbox extends Component
{
   protected string $type = 'checkbox';
   protected array|Collection $choices = [];
   protected bool $required = false;
   protected ?string $pattern = null;
   protected array $options = [];
   protected bool $disabled = false;
   protected bool $inline = false;
   protected bool $checked = false;
   protected ?string $value = '1';

   public function __construct(string $name, string $label, array|Collection $choices, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->choices = is_array($choices) ? $choices : $choices->toArray() ?? [];
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->disabled = $options['disabled'] ?? false;
      $this->inline = $options['inline'] ?? false;
      $this->checked = $options['checked'] ?? false;
      $this->value = $options['value'] ?? '1';
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

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      if ($this->pattern) {
         $attributes['pattern'] = $this->pattern;
      }

      if ($this->disabled) {
         $attributes['disabled'] = $this->disabled;
      }

      if ($this->checked || $this->value === $this->getOption('value')) {
         $attributes['checked'] = 'checked';
      }

      if ($this->inline) {
         $wrapperClass = $this->inline ? 'inline-flex items-center mr-4' : 'flex items-center';
      }

      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '" class="' . $wrapperClass . '">' . $this->label;
      $html .= '<input ' . $this->buildAttributes($attributes) . '>';
      $html .= '</label>';

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
