<?php

namespace IronFlow\Forms\Components;

use IronFlow\Validation\Validator;

class DatePicker extends Component
{
   protected string $type = 'date';
   protected ?string $placeholder = null;
   protected bool $required = false;
   protected ?string $pattern = null;
   protected ?string $min = null;
   protected ?string $max = null;

   public function __construct(string $name, string $label, array $options = [], bool $showTime,array|Validator $validator = [])
   {
      parent::__construct($name, $label, $options, $validator);

      $this->type = $showTime ? "datetime-local" : "date";
      $this->placeholder = $options['placeholder'] ?? null;
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->min = $options['min'] ?? null;
      $this->max = $options['max'] ?? null;
   }

   /**
    * Récuperer l'attribut name
    *
    * @return string
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * Rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      $attributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300'
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

      if ($this->getError()) {
         $html .= '<div class="error-message">' . $this->getError() . '</div>';
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
