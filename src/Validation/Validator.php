<?php

declare(strict_types=1);

namespace IronFlow\Validation;

class Validator
{
   /**
    * Les données à valider
    */
   private array $data;

   /**
    * Les règles de validation
    */
   private array $rules;

   /**
    * Les erreurs de validation
    */
   private array $errors = [];

   /**
    * Les messages d'erreur personnalisés
    */
   private array $customMessages = [];

   /**
    * Les données validées
    */
   private array $validated = [];

   /**
    * Crée une nouvelle instance du validateur
    */
   public static function make(array $data, array $rules, array $messages = []): self
   {
      return new self($data, $rules, $messages);
   }

   /**
    * Constructeur
    */
   public function __construct(array $data, array $rules, array $messages = [])
   {
      $this->data = $data;
      $this->rules = $rules;
      $this->customMessages = $messages;
   }

   /**
    * Valide les données
    */
   public function validate(): bool
   {
      foreach ($this->rules as $field => $rules) {
         $value = $this->data[$field] ?? null;

         foreach ($rules as $rule) {
            if (!$this->validateRule($field, $value, $rule)) {
               break;
            }
         }
      }

      return empty($this->errors);
   }

   /**
    * Valide une règle
    */
   protected function validateRule(string $field, mixed $value, string|Rule $rule): bool
   {
      if (is_string($rule)) {
         $rule = $this->parseRule($rule);
      }

      if (!$rule instanceof Rule) {
         throw new \InvalidArgumentException('La règle doit être une instance de Rule ou une chaîne de caractères valide');
      }

      $result = $rule->validate($field, $value, $rule->getParameters(), $this->data);

      if (!$result) {
         $this->addError($field, $rule->getMessage());
      }

      if ($result) {
         $this->validated[$field] = $value;
      }

      return $result;
   }

   /**
    * Parse une règle sous forme de chaîne
    */
   protected function parseRule(string $rule): Rule
   {
      $parts = explode(':', $rule);
      $ruleName = $parts[0];
      $parameters = [];

      if (isset($parts[1])) {
         $parameters = explode(',', $parts[1]);
      }

      $ruleClass = 'IronFlow\\Validation\\Rules\\' . ucfirst($ruleName);

      if (!class_exists($ruleClass)) {
         throw new \InvalidArgumentException("Règle de validation inconnue : {$ruleName}");
      }

      return new $ruleClass($parameters);
   }

   /**
    * Ajoute une erreur
    */
   protected function addError(string $field, string $message): void
   {
      if (!isset($this->errors[$field])) {
         $this->errors[$field] = [];
      }
      $this->errors[$field][] = $message;
   }

   /**
    * Récupère les erreurs
    */
   public function getErrors(): array
   {
      return $this->errors;
   }

   /**
    * Récupère la première erreur
    */
   public function getFirstError(): ?string
   {
      if (empty($this->errors)) {
         return null;
      }

      $firstField = array_key_first($this->errors);
      return $this->errors[$firstField][0] ?? null;
   }

   /**
    * Vérifie s'il y a des erreurs
    */
   public function hasErrors(): bool
   {
      return !empty($this->errors);
   }

   /**
    * Vérifie si la validation a réussi
    */
   public function passes(): bool
   {
      return $this->validate();
   }

   /**
    * Vérifie si la validation a échoué
    */
   public function fails(): bool
   {
      return !$this->passes();
   }

   /**
    * Récupère les données validées
    */
   public function validated(): array
   {
      return $this->validated;
   }

   /**
    * Récupère les erreurs de validation
    */
   public function errors(): array
   {
      return $this->errors;
   }
}
