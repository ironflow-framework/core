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

      return "
            <div class='form-group'>
                <label for='{$this->name}'>{$this->label}</label>
                <textarea 
                    name='{$this->name}'
                    id='{$this->name}'
                    rows='{$this->rows}'
                    placeholder='{$this->placeholder}'
                    class='form-control{$errorClass}'
                    {$this->renderAttributes()}
                >{$value}</textarea>
                {$errorMessage}
            </div>
        ";
   }
}
