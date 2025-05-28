<?php

declare(strict_types=1);

namespace IronFlow\Validation;

use IronFlow\Validation\Rules\RuleInterface;
use IronFlow\Validation\Exceptions\ValidationException;

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
    * Les attributs personnalisés
    */
   private array $customAttributes = [];

   /**
    * Les données validées
    */
   private array $validated = [];

   /**
    * Crée une nouvelle instance du validateur
    */
   public static function make(array $data, array $rules, array $messages = [], array $attributes = []): self
   {
      return new self($data, $rules, $messages, $attributes);
   }

   /**
    * Constructeur
    */
   public function __construct(array $data, array $rules, array $messages = [], array $attributes = [])
   {
      $this->data = $data;
      $this->rules = $this->parseRules($rules);
      $this->customMessages = $messages;
      $this->customAttributes = $attributes;
   }

   /**
    * Valide les données
    */
   public function validate(): array
   {
      $this->errors = [];
      $this->validated = [];

      foreach ($this->rules as $field => $rules) {
         $value = $this->getValue($field);

         foreach ($rules as $rule) {
            if (!$this->validateRule($field, $value, $rule)) {
               break;
            }
         }

         if (!isset($this->errors[$field])) {
            $this->validated[$field] = $value;
         }
      }

      if (!empty($this->errors)) {
         throw new ValidationException('Les données fournies sont invalides.', $this->errors);
      }

      return $this->validated;
   }

   /**
    * Valide une règle
    */
   private function validateRule(string $field, mixed $value, RuleInterface $rule): bool
   {
      if (!$rule->passes($field, $value, $this->data)) {
         $this->addError($field, $this->formatMessage($field, $rule));
         return false;
      }

      return true;
   }

   /**
    * Récupère la valeur d'un champ
    */
   private function getValue(string $field): mixed
   {
      $value = $this->data[$field] ?? null;

      if (is_string($value)) {
         $value = trim($value);
      }

      return $value;
   }

   /**
    * Parse les règles
    */
   private function parseRules(array $rules): array
   {
      $parsed = [];

      foreach ($rules as $field => $fieldRules) {
         $parsed[$field] = [];

         $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

         foreach ($fieldRules as $rule) {
            if ($rule instanceof RuleInterface) {
               $parsed[$field][] = $rule;
               continue;
            }

            $parsed[$field][] = $this->createRule($rule);
         }
      }

      return $parsed;
   }

   /**
    * Crée une règle
    */
   private function createRule(string $rule): RuleInterface
   {
      if (str_contains($rule, ':')) {
         [$rule, $parameters] = explode(':', $rule, 2);
         $parameters = explode(',', $parameters);
      } else {
         $parameters = [];
      }

      $ruleClass = 'IronFlow\\Validation\\Rules\\' . ucfirst($rule) . 'Rule';

      if (!class_exists($ruleClass)) {
         throw new ValidationException("Règle de validation inconnue: {$rule}");
      }

      return new $ruleClass($parameters);
   }

   /**
    * Formate un message d'erreur
    */
   private function formatMessage(string $field, RuleInterface $rule): string
   {
      $message = $this->customMessages[$field . '.' . $rule->getName()] ??
         $this->customMessages[$rule->getName()] ??
         $rule->getMessage();

      return str_replace(
         [':attribute', ':field'],
         [$this->customAttributes[$field] ?? $field, $field],
         $message
      );
   }

   /**
    * Ajoute une erreur
    */
   private function addError(string $field, string $message): void
   {
      if (!isset($this->errors[$field])) {
         $this->errors[$field] = [];
      }

      $this->errors[$field][] = $message;
   }

   /**
    * Récupère les erreurs
    *
    * @return array
    */
   public function errors(): array
   {
      return $this->errors;
   }

   /**
    * Vérifie si la validation a échoué
    */
   public function fails(): bool
   {
      try {
         $this->validate();
         return false;
      } catch (ValidationException $e) {
         return true;
      }
   }

   /**
    * Vérifie si la validation a réussi
    */
   public function passes(): bool
   {
      return !$this->fails();
   }

   /**
    * Récupère les données validées
    */
   public function validated(): array
   {
      if (empty($this->validated)) {
         $this->validate();
      }

      return $this->validated;
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
    * Ajoute des règles conditionnelles
    */
   public function sometimes(string $field, array|string $rules, callable $callback): self
   {
      if ($callback($this->data)) {
         $this->rules[$field] = $this->parseRules([$field => $rules])[$field];
      }

      return $this;
   }
}
