<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

abstract class Component
{
   /**
    * Nom du champ
    *
    * @var string
    */
   protected string $name;

   /**
    * Label du champ
    *
    * @var string
    */
   protected string $label;

   /**
    * Attributs HTML
    *
    * @var array
    */
   protected array $attributes = [];

   /**
    * Valeur du champ
    *
    * @var mixed
    */
   protected mixed $value = null;

   /**
    * Erreur du champ
    *
    * @var string|null
    */
   protected ?string $error = null;

   /**
    * Constructeur
    *
    * @param string $name Nom du champ
    * @param string $label Label du champ
    * @param array $attributes Attributs HTML
    */
   public function __construct(string $name, string $label = '', array $attributes = [])
   {
      $this->name = $name;
      $this->label = $label;
      $this->attributes = $attributes;
   }

   /**
    * Définit la valeur du champ
    *
    * @param mixed $value
    * @return self
    */
   public function setValue(mixed $value): self
   {
      $this->value = $value;
      return $this;
   }

   /**
    * Récupère la valeur du champ
    *
    * @return mixed
    */
   public function getValue(): mixed
   {
      return $this->value;
   }

   /**
    * Définit l'erreur du champ
    *
    * @param string $error
    * @return self
    */
   public function setError(string $error): self
   {
      $this->error = $error;
      return $this;
   }

   /**
    * Récupère l'erreur du champ
    *
    * @return string|null
    */
   public function getError(): ?string
   {
      return $this->error;
   }

   /**
    * Ajoute un attribut HTML
    *
    * @param string $name
    * @param string $value
    * @return self
    */
   public function attribute(string $name, string $value): self
   {
      $this->attributes[$name] = $value;
      return $this;
   }

   /**
    * Rendu des attributs HTML
    *
    * @return string
    */
   protected function renderAttributes(): string
   {
      $html = '';
      foreach ($this->attributes as $name => $value) {
         $html .= " {$name}='{$value}'";
      }
      return $html;
   }

   /**
    * Rendu du composant
    *
    * @return string
    */
   abstract public function render(): string;
}
