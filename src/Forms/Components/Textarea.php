<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

class Textarea extends Component
{
   /**
    * Nombre de lignes
    *
    * @var int
    */
   protected int $rows = 3;

   /**
    * Placeholder
    *
    * @var string
    */
   protected string $placeholder = '';

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
    * Définit le nombre de lignes
    *
    * @param int $rows
    * @return self
    */
   public function rows(int $rows): self
   {
      $this->rows = $rows;
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
      $this->placeholder = $placeholder;
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
      $textareaClasses = $this->combineClasses('textarea');
      if ($error) {
         $textareaClasses .= ' ' . $this->getErrorClasses('input');
      }

      $html = "
         <div class='" . $this->getDefaultClasses('container') . "'>
            <label for='{$this->name}' class='" . $this->getDefaultClasses('label') . "'>{$this->label}</label>
            <textarea
               name='{$this->name}'
               id='{$this->name}'
               class='{$textareaClasses}'
               placeholder='{$this->placeholder}'
               rows='{$this->rows}'
               " . $this->renderAttributes() . "
            >{$value}</textarea>
            " . ($error ? "<p class='" . $this->getDefaultClasses('error') . "'>{$error}</p>" : "") . "
         </div>";

      return $html;
   }
}
