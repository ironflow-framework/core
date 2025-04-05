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
      $checkboxClasses = $this->combineClasses('checkbox');
      if ($error) {
         $checkboxClasses .= ' ' . $this->getErrorClasses('input');
      }

      $checked = $value ? ' checked' : '';

      $html = "
         <div class='" . $this->getDefaultClasses('container') . " flex items-start'>
            <div class='flex h-5 items-center'>
               <input
                  type='checkbox'
                  name='{$this->name}'
                  id='{$this->name}'
                  class='{$checkboxClasses}'
                  {$checked}
                  " . $this->renderAttributes() . "
               />
            </div>
            <div class='ml-3 text-sm'>
               <label for='{$this->name}' class='" . $this->getDefaultClasses('label') . "'>{$this->label}</label>
               " . ($error ? "<p class='" . $this->getDefaultClasses('error') . "'>{$error}</p>" : "") . "
            </div>
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
