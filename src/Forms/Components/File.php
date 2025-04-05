<?php

namespace IronFlow\Forms\Components;

class File extends Component
{
   protected string $type = 'file';
   protected ?string $placeholder = null;
   protected bool $required = false;
   protected ?string $pattern = null;
   protected ?array $accept = null;
   protected int $maxSize = 0;
   protected bool $multiple = false;
   protected bool $disabled = false;

   public function __construct(string $name, string $label, array $options = [], ?array $accept = null)
   {
      parent::__construct($name, $label, $options);

      $this->placeholder = $options['placeholder'] ?? null;
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->accept = $accept ?? null;
      $this->maxSize = $options['maxSize'] ?? 0;
      $this->multiple = $options['multiple'] ?? false;
      $this->disabled = $options['disabled'] ?? false;
   }

   public function maxSize(int $size): self
   {
      $this->maxSize = $size;
      return $this;
   }

   public function multiple(bool $multiple = true): self
   {
      $this->multiple = $multiple;
      return $this;
   }

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function disabled(bool $disabled = true): self
   {
      $this->disabled = $disabled;
      return $this;
   }

   public function accept(array $accept): self
   {
      $this->accept = $accept;
      return $this;
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

   public function render(): string
   {
      $attributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300',
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

      if (!empty($this->accept)) {
         $attributes['accept'] = implode(',', $this->accept);
      }

      if ($this->multiple) {
         $attributes['multiple'] = 'multiple';
      }

      if ($this->disabled) {
         $attributes['disabled'] = 'disabled';
      }


      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '<input ' . $this->buildAttributes($attributes) . '>';

      if ($this->getError()) {
         $html .= '<div class="error-message">' .  $this->getError() . '</div>';
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
