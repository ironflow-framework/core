<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

use IronFlow\Database\Collection;

class Radio extends Component
{
   /**
    * Options du radio
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
    * Affichage en ligne
    *
    * @var bool
    */
   protected bool $inline = false;

   /**
    * Constructeur
    *
    * @param string $name Nom du champ
    * @param string $label Label du champ
    * @param array $attributes Attributs HTML
    */
   public function __construct(string $name, array|Collection $choices, string $label = '', array $attributes = [])
   {
      parent::__construct($name, $label, $attributes);

      $this->options(array_merge($this->options, $choices));
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
      $radioClasses = $this->combineClasses('radio');
      if ($error) {
         $radioClasses .= ' ' . $this->getErrorClasses('input');
      }

      $html = "
         <div class='" . $this->getDefaultClasses('container') . "'>
            <label class='" . $this->getDefaultClasses('label') . "'>{$this->label}</label>
            <div class='mt-2 space-y-2'>";

      foreach ($this->options as $optionValue => $optionLabel) {
         $checked = $value == $optionValue ? ' checked' : '';
         $html .= "
            <div class='flex items-center'>
               <input
                  type='radio'
                  name='{$this->name}'
                  id='{$this->name}_{$optionValue}'
                  value='{$optionValue}'
                  class='{$radioClasses}'
                  {$checked}
                  " . $this->renderAttributes() . "
               />
               <label for='{$this->name}_{$optionValue}' class='ml-3 block text-sm font-medium text-gray-700'>
                  {$optionLabel}
               </label>
            </div>";
      }

      $html .= "
            </div>
            " . ($error ? "<p class='" . $this->getDefaultClasses('error') . "'>{$error}</p>" : "") . "
         </div>";

      return $html;
   }
}
