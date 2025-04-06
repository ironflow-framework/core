<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;
use IronFlow\Validation\Validator;
use Symfony\Component\VarDumper\VarDumper;

class Select extends Component
{
   /**
    * Options du select
    *
    * @var array
    */
   protected array|Collection $options = [];

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
   public function __construct(string $name, string $label = '', array|Collection $options = [], array $attributes = [], array|Validator $validator = [])
   {
      parent::__construct($name, $label, $attributes, $validator);
      $this->options($options);
   }

   /**
    * Définit les options
    *
    * @param array|Collection $options
    * @return self
    */
   public function options(array|Collection $options): self
   {
      $this->options = is_array($options) ? $options : $options->toArray();
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
      $value = $this->getValue() ?? $this->defaultValue;
      $error = $this->getError();
      
      // Combine les classes de base avec les classes d'erreur si nécessaire
      $selectClasses = $this->combineClasses('select');
      if ($error) {
         $selectClasses .= ' ' . $this->getErrorClasses('input');
      }

      $options = '';
      foreach ($this->options as $optionValue => $optionLabel) {
         $selected = $value == $optionValue ? ' selected' : '';
         $optionValue = is_string($optionValue) ? $optionValue : $optionValue + 1;
         $options .= "<option value='{$optionValue}'{$selected}>{$optionLabel}</option>";
      }

      $html = "
         <div class='" . $this->getDefaultClasses('container') . "'>
            <label for='{$this->name}' class='" . $this->getDefaultClasses('label') . "'>{$this->label}</label>
            <select
               name='{$this->name}'
               id='{$this->name}'
               class='{$selectClasses}'
               " . $this->renderAttributes() . "
            >
               {$options}
            </select>
            " . ($error ? "<p class='" . $this->getDefaultClasses('error') . "'>{$error}</p>" : "") . "
         </div>";

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
