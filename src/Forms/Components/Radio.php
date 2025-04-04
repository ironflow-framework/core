<?php

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;

class Radio extends Component
{
   protected string $type = 'radio';
   protected array|Collection $choices = [];
   protected bool $required = false;
   protected ?string $pattern = null;
   protected array $options = [];
   protected bool $disabled = false;
   protected bool $inline = false;

   public function __construct(string $name, string $label, array|Collection $choices, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->choices = is_array($choices) ? $choices : $choices->toArray() ?? [];
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->disabled = $options['disabled'] ?? false;
      $this->inline = $options['inline'] ?? false;
   }

   public function render(): string
   {
      $html = '<div class="form-group">';
      $html .= '<label>' . $this->label . '</label>';

      foreach ($this->choices as $value => $label) {
         $attributes = [
            'type' => 'radio',
            'name' => $this->name,
            'id' => $this->name . '_' . $value,
            'value' => $value,
            'class' => $this->getOption('class', 'form-check-input'),
         ];

         if ($this->value == $value) {
            $attributes['checked'] = 'checked';
         }

         $html .= '<div class="form-check' . ($this->inline ? ' form-check-inline' : '') . '">';
         $html .= '<input ' . $this->buildAttributes($attributes) . '>';
         $html .= '<label class="form-check-label" for="' . $this->name . '_' . $value . '">' . $label . '</label>';
         $html .= '</div>';
      }

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
