<?php

declare(strict_types=1);

namespace IronFlow\Validation;

use IronFlow\Support\Utils\Str;
use IronFlow\Validation\Rules\Email;

abstract class Validator
{
   protected array $data = [];
   protected array $rules = [];
   protected array $messages = [];
   protected array $errors = [];
   protected array $customRules = [];
   protected array $ruleClasses = [];

   public function __construct(array $data = [], array $rules = [], array $messages = [])
   {
      $this->data = $data;
      $this->rules = $rules ?: $this->rules();
      $this->messages = $messages ?: $this->messages();
      $this->registerDefaultRules();
   }

   abstract public function rules(): array;

   abstract public function messages(): array;

   /**
    * Enregistre les règles par défaut
    */
   protected function registerDefaultRules(): void
   {
      $this->ruleClasses = [
         'required' => Rules\Required::class,
         'email' => Rules\Email::class,
         'numeric' => Rules\Number::class,
         'min' => Rules\StringLength::class,
         'max' => Rules\StringLength::class,
         'date' => Rules\Date::class,
         'regex' => Rules\Regex::class,
         'in_array' => Rules\InArray::class,
         'match' => Rules\IsMatch::class,
         'color' => Rules\ColorValidator::class,
         'file' => Rules\FileValidator::class,
      ];
   }

   /**
    * Crée une instance de Validator
    *
    * @param array $data Données du formulaire
    * @param array $rules Règles de validation
    * @param array $messages Messages de validation
    * @return self
    */
   public static function make(array $data = [], array $rules = [], array $messages = []): self
   {
      return new static($data, $rules, $messages);
   }

   /**
    * Valide les données
    *
    * @param array|null $data Données à valider (optionnel)
    * @return bool True si la validation réussit, false sinon
    */
   public function validate(?array $data = null): bool
   {
      $this->errors = [];

      if ($data !== null) {
         $this->data = $data;
      }

      foreach ($this->rules as $field => $rules) {
         $rules = is_string($rules) ? explode('|', $rules) : $rules;

         foreach ($rules as $rule) {
            $parameters = [];

            if (is_string($rule)) {
               if (strpos($rule, ':') !== false) {
                  [$rule, $parameter] = explode(':', $rule, 2);
                  $parameters = explode(',', $parameter);
               }
            }

            $ruleClass = $this->getRuleClass($rule);

            if ($ruleClass) {
               $ruleInstance = new $ruleClass($parameters);
               $ruleInstance->setAttribute('field', $field);
               $ruleInstance->setAttribute('value', $this->getValue($field));

               if (!$ruleInstance->validate($field, $this->getValue($field), $parameters, $this->data)) {
                  $this->addError($field, $rule, $parameters, $ruleInstance);
               }
            } elseif (isset($this->customRules[$rule])) {
               if (!$this->customRules[$rule]($field, $this->getValue($field), $parameters, $this->data)) {
                  $this->addError($field, $rule, $parameters);
               }
            }
         }
      }

      return empty($this->errors);
   }

   /**
    * Récupère la classe de règle correspondante
    */
   protected function getRuleClass(string $rule): ?string
   {
      return $this->ruleClasses[$rule] ?? null;
   }

   /**
    * Enregistre une nouvelle règle personnalisée
    */
   public function addRule(string $name, string $class): self
   {
      $this->ruleClasses[$name] = $class;
      return $this;
   }

   /**
    * Enregistre une règle personnalisée avec une fonction de callback
    */
   public function addCustomRule(string $name, callable $callback): self
   {
      $this->customRules[$name] = $callback;
      return $this;
   }

   /**
    * Renvoie toutes les erreurs
    */
   public function errors(): array
   {
      return $this->errors;
   }

   /**
    * Récupère la valeur d'un champ
    */
   protected function getValue(string $field)
   {
      return $this->data[$field] ?? null;
   }

   /**
    * Ajoute une erreur pour un champ
    */
   protected function addError(string $field, string $rule, array $parameters = [], ?object $ruleInstance = null): void
   {
      $message = $this->messages[$field . '.' . $rule]
         ?? $this->messages[$field]
         ?? ($ruleInstance && method_exists($ruleInstance, 'getMessage') ? $ruleInstance->getMessage() : $this->getDefaultMessage($field, $rule, $parameters));

      $this->errors[$field][] = $this->formatMessage($message, $field, $parameters);
   }

   /**
    * Formate un message d'erreur en remplaçant les variables
    */
   protected function formatMessage(string $message, string $field, array $parameters): string
   {
      $replacements = [
         ':field' => $field,
         ':min' => $parameters[0] ?? '',
         ':max' => $parameters[0] ?? '',
      ];

      foreach ($replacements as $key => $value) {
         $message = str_replace($key, $value, $message);
      }

      return $message;
   }

   /**
    * Obtient le message d'erreur par défaut pour une règle
    */
   protected function getDefaultMessage(string $field, string $rule, array $parameters): string
   {
      $messages = [
         'required' => 'Le champ :field est requis.',
         'email' => 'Le champ :field doit être une adresse email valide.',
         'min' => 'Le champ :field doit contenir au moins :min caractères.',
         'max' => 'Le champ :field ne peut pas dépasser :max caractères.',
         'numeric' => 'Le champ :field doit être un nombre.',
         'alpha' => 'Le champ :field ne peut contenir que des lettres.',
         'alpha_num' => 'Le champ :field ne peut contenir que des lettres et des chiffres.',
         'url' => 'Le champ :field doit être une URL valide.',
         'confirmed' => 'La confirmation du champ :field ne correspond pas.',
         'date' => 'Le champ :field doit être une date valide.',
         'regex' => 'Le format du champ :field est invalide.',
         'in_array' => 'La valeur sélectionnée pour :field est invalide.',
         'match' => 'Le champ :field ne correspond pas.',
         'color' => 'Le champ :field doit être une couleur valide.',
         'file' => 'Le champ :field doit être un fichier valide.',
      ];

      return $messages[$rule] ?? 'Le champ :field est invalide.';
   }

   /**
    * Vérifie si le champ existe dans les données
    */
   public function hasField(string $field): bool
   {
      return isset($this->data[$field]);
   }

   /**
    * Vérifie si le champ est vide
    */
   public function isEmpty(string $field): bool
   {
      return empty($this->data[$field]);
   }

   /**
    * Récupère la valeur d'un champ avec une valeur par défaut
    */
   public function get(string $field, $default = null)
   {
      return $this->data[$field] ?? $default;
   }

   /**
    * Vérifie si la validation a échoué
    */
   public function fails(): bool
   {
      return !$this->validate();
   }

   /**
    * Vérifie si la validation a réussi
    */
   public function passes(): bool
   {
      return $this->validate();
   }

   /**
    * Récupère le premier message d'erreur pour un champ
    */
   public function first(string $field): ?string
   {
      return $this->errors[$field][0] ?? null;
   }

   /**
    * Récupère tous les messages d'erreur pour un champ
    */
   public function getErrors(string $field): array
   {
      return $this->errors[$field] ?? [];
   }
}
