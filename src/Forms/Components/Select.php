<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;

class Select extends Component
{
   /**
    * Options du select
    *
    * @var array
    */
   protected array $options = [];

   /**
    * Valeur par défaut
    *
    * @var mixed
    */
   protected mixed $defaultValue = null;

   /**
    * Constructeur
    *
    * @param string $name Nom du champ
    * @param string $label Label du champ
    * @param array $attributes Attributs HTML
    */
   public function __construct(string $name, string $label = '', array $attributes = [])
   {
      parent::__construct($name, $label, $attributes);
   }

   /**
    * Définit les options
    *
    * @param array $options
    * @return self
    */
   public function options(array $options): self
   {
      $this->options = $options;
      return $this;
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
    * Rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      $value = $this->getValue() ?? $this->defaultValue;
      $error = $this->getError();
      $errorClass = $error ? ' is-invalid' : '';
      $errorMessage = $error ? "<div class='invalid-feedback'>{$error}</div>" : '';

      $options = '';
      foreach ($this->options as $optionValue => $optionLabel) {
         $selected = $value == $optionValue ? ' selected' : '';
         $options .= "<option value='{$optionValue}'{$selected}>{$optionLabel}</option>";
      }

      return "
            <div class='form-group'>
                <label for='{$this->name}'>{$this->label}</label>
                <select 
                    name='{$this->name}'
                    id='{$this->name}'
                    class='form-control{$errorClass}'
                    {$this->renderAttributes()}
                >
                    {$options}
                </select>
                {$errorMessage}
            </div>
        ";
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
