<?php

declare(strict_types=1);

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

   /**
    * Valeur par défaut
    *
    * @var mixed
    */
   protected mixed $defaultValue = false;

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

   /**
    * Définit la valeur par défaut
    *
    * @param mixed $value
    * @return self
    */
   public function defaultValue(mixed $value): self
   {
      $this->defaultValue = $value;
      return $this;
   }

   /**
    * Définit l'affichage en ligne
    *
    * @param bool $inline
    * @return self
    */
   public function inline(bool $inline = true): self
   {
      $this->inline = $inline;
      return $this;
   }

   public function render(): string
   {
      $value = $this->getValue() ?? $this->defaultValue;
      $error = $this->getError();
      $errorClass = $error ? ' is-invalid' : '';
      $errorMessage = $error ? "<div class='invalid-feedback'>{$error}</div>" : '';
      $wrapperClass = $this->inline ? 'form-check-inline' : 'form-check';

      $attributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded',
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
         $attributes['disabled'] = 'disabled';
         $attributes['class'] .= ' opacity-50 cursor-not-allowed';
      }

      if ($this->checked || $this->value === '1') {
         $attributes['checked'] = 'checked';
      }

      $html = '<div class="space-y-2">';
      $html .= '<div class="' . ($this->inline ? 'inline-flex items-center mr-4' : 'flex items-center') . '">';
      $html .= '<input ' . $this->buildAttributes($attributes) . '>';
      $html .= '<label class="ml-2 block text-sm text-gray-700" for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '</div>';

      if ($error) {
         $html .= '<p class="mt-1 text-sm text-red-600">' . $error . '</p>';
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
