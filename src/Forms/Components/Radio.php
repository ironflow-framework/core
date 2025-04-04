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
   public function __construct(string $name, string $label = '', array $attributes = [])
   {
      parent::__construct($name, $label, $attributes);
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
      $wrapperClass = $this->inline ? 'form-check-inline' : 'form-check';

      $radios = '';
      foreach ($this->options as $optionValue => $optionLabel) {
         $checked = $value == $optionValue ? ' checked' : '';
         $radios .= "
            <div class='" . ($this->inline ? 'inline-flex items-center mr-4' : 'flex items-center') . "'>
               <input 
                  type='radio'
                  name='{$this->name}'
                  id='{$this->name}_{$optionValue}'
                  value='{$optionValue}'
                  {$checked}
                  class='h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300'
                  {$this->renderAttributes()}
               >
               <label class='ml-2 block text-sm text-gray-700' for='{$this->name}_{$optionValue}'>{$optionLabel}</label>
            </div>
         ";
      }

      return "
         <div class='space-y-2'>
            <label class='block text-sm font-medium text-gray-700'>{$this->label}</label>
            <div class='space-y-2'>
               {$radios}
            </div>
            " . ($error ? "<p class='mt-1 text-sm text-red-600'>{$error}</p>" : '') . "
         </div>
      ";
   }
}
