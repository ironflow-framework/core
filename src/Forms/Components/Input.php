<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

use IronFlow\Validation\Validator;

class Input extends Component
{
   /**
    * Type de l'input
    *
    * @var string
    */
   protected string $type = 'text';

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
   public function __construct(string $name, string $label = '', array $attributes = [], array|Validator $validator = [])
   {
      parent::__construct($name, $label, $attributes, $validator);
   }

   /**
    * Définit le type de l'input
    *
    * @param string $type
    * @return self
    */
   public function type(string $type): self
   {
      $this->type = $type;
      return $this;
   }

   /**
    * Définit le placeholder
    *
    * @param string $placeholder
    * @return self
    */
   public function placeholder(string $placeholder): self
   {
      $this->attribute('placeholder', $placeholder);
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
      $inputClasses = $this->combineClasses('input');
      if ($error) {
         $inputClasses .= ' ' . $this->getErrorClasses('input');
      }

      $html = "
         <div class='" . $this->getDefaultClasses('container') . "'>
            <label for='{$this->name}' class='" . $this->getDefaultClasses('label') . "'>{$this->label}</label>
            <input
               type='{$this->type}'
               name='{$this->name}'
               id='{$this->name}'
               value='{$value}'
               class='{$inputClasses}'
               " . $this->renderAttributes() . "
            />
            " . ($error ? "<p class='" . $this->getDefaultClasses('error') . "'>{$error}</p>" : "") . "
         </div>";

      return $html;
   }
}
