<?php

declare(strict_types=1);

namespace IronFlow\Forms\Components;

use IronFlow\Validation\Validator;

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
    * Règles de validation
    *
    * @var array
    */
   protected array|Validator $rules = [];

   /**
    * Classes Tailwind par défaut pour les composants
    */
   protected array $defaultClasses = [
       'container' => 'mb-6',
       'label' => 'block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1',
       'input' => 'block w-full px-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors duration-200',
       'input-error' => 'border-red-500 dark:border-red-400 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400',
       'error' => 'mt-2 text-sm text-red-600 dark:text-red-400',
       'button' => 'inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200',
       'checkbox' => 'h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 bg-white dark:bg-gray-700 transition-colors duration-200',
       'radio' => 'h-5 w-5 border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 bg-white dark:bg-gray-700 transition-colors duration-200',
       'select' => 'block w-full px-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors duration-200',
       'file' => 'block w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors duration-200',
       'textarea' => 'block w-full px-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors duration-200 resize-y',
       'color-picker' => 'h-12 w-full rounded-lg border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors duration-200',
       'date-picker' => 'block w-full px-4 py-3 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 transition-colors duration-200'
   ];

   /**
    * Constructeur
    *
    * @param string $name Nom du champ
    * @param string $label Label du champ
    * @param array $attributes Attributs HTML
    */
   public function __construct(string $name, string $label = '', array $attributes = [], array|Validator $validator = [])
   {
      $this->name = $name;
      $this->label = $label;
      $this->attributes = $attributes;
      $this->rules = $validator;
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
    * Ajoute des règles de validation
    *
    * @param array $rules
    * @return self
    */
   public function rules(array $rules): self
   {
      $this->rules = $rules;
      return $this;
   }

   /**
    * Valide la valeur du champ
    *
    * @return bool
    */
   public function validate(): bool
   {
      if (empty($this->rules)) {
         return true;
      }
      
      if (!$this->rules instanceof Validator) {
         $validator = Validator::make([$this->name => $this->value], [$this->name => $this->rules]);
      }
      else {
         $validator = $this->rules;
      }

      $validator->validate();

      if ($validator->fails()) {
         $this->error = $validator->getFirstError();
         return false;
      }

      return true;
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
         if (is_string($value)) {
            $html .= " {$name}='{$value}'";
         }

         break;
      }
      return $html;
   }

   /**
    * Récupère les classes CSS par défaut pour un type de composant
    *
    * @param string $type
    * @return string
    */
   protected function getDefaultClasses(string $type): string
   {
       return $this->defaultClasses[$type] ?? '';
   }

   /**
    * Combine les classes par défaut avec les classes personnalisées
    *
    * @param string $type
    * @param string $additionalClasses
    * @return string
    */
   protected function combineClasses(string $type, string $additionalClasses = ''): string
   {
       $defaultClasses = $this->getDefaultClasses($type);
       return trim($defaultClasses . ' ' . $additionalClasses);
   }

   /**
    * Récupère les classes d'erreur
    *
    * @param string $type
    * @return string
    */
   protected function getErrorClasses(string $type): string
   {
       return $this->defaultClasses[$type . '-error'] ?? '';
   }

   /**
    * Rendu du composant
    *
    * @return string
    */
   abstract public function render(): string;

   /**
    * Récupère une option
    *
    * @param string $option
    * @param string $default
    * @return string
    */
   public function getOption(string $option, string $default)
   {
      return $this->attributes[$option] ?? $default;
   }
}
