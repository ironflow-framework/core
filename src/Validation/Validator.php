<?php

declare(strict_types=1);

namespace IronFlow\Validation;

class Validator
{
   protected array $data = [];
   protected array $rules = [];
   protected array $messages = [];
   protected array $errors = [];
   protected array $customRules = [];

   public function __construct(array $data = [], array $rules = [], array $messages = [])
   {
      $this->data = $data;
      $this->rules = $rules;
      $this->messages = $messages;
   }

   public function validate($value = null, array $data = []): bool
   {
      $this->errors = [];

      if ($value !== null) {
         return $this->validateValue($value, $data);
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

            $method = 'validate' . ucfirst($rule);

            if (method_exists($this, $method)) {
               if (!$this->$method($field, $parameters)) {
                  $this->addError($field, $rule, $parameters);
               }
            } elseif (isset($this->customRules[$rule])) {
               if (!$this->customRules[$rule]($field, $this->getValue($field), $parameters)) {
                  $this->addError($field, $rule, $parameters);
               }
            }
         }
      }

      return empty($this->errors);
   }

   protected function validateValue($value, array $data = []): bool
   {
      return true;
   }

   public function addRule(string $name, callable $callback): self
   {
      $this->customRules[$name] = $callback;
      return $this;
   }

   public function errors(): array
   {
      return $this->errors;
   }

   protected function getValue(string $field)
   {
      return $this->data[$field] ?? null;
   }

   protected function addError(string $field, string $rule, array $parameters = []): void
   {
      $message = $this->messages[$field . '.' . $rule]
         ?? $this->messages[$field]
         ?? $this->getDefaultMessage($field, $rule, $parameters);

      $this->errors[$field][] = $message;
   }

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
      ];

      $message = $messages[$rule] ?? 'Le champ :field est invalide.';
      $replacements = [
         ':field' => $field,
         ':min' => $parameters[0] ?? '',
         ':max' => $parameters[0] ?? '',
      ];

      return str_replace(array_keys($replacements), array_values($replacements), $message);
   }

   // Règles de validation de base
   protected function validateRequired(string $field): bool
   {
      $value = $this->getValue($field);
      return !empty($value) || $value === '0' || $value === 0;
   }

   protected function validateEmail(string $field): bool
   {
      return filter_var($this->getValue($field), FILTER_VALIDATE_EMAIL) !== false;
   }

   protected function validateMin(string $field, array $parameters): bool
   {
      $value = $this->getValue($field);
      $min = $parameters[0] ?? 0;
      return strlen($value) >= $min;
   }

   protected function validateMax(string $field, array $parameters): bool
   {
      $value = $this->getValue($field);
      $max = $parameters[0] ?? PHP_INT_MAX;
      return strlen($value) <= $max;
   }

   protected function validateNumeric(string $field): bool
   {
      return is_numeric($this->getValue($field));
   }

   protected function validateAlpha(string $field): bool
   {
      return ctype_alpha($this->getValue($field));
   }

   protected function validateAlphaNum(string $field): bool
   {
      return ctype_alnum($this->getValue($field));
   }

   protected function validateUrl(string $field): bool
   {
      return filter_var($this->getValue($field), FILTER_VALIDATE_URL) !== false;
   }

   protected function validateConfirmed(string $field): bool
   {
      $value = $this->getValue($field);
      $confirmation = $this->getValue($field . '_confirmation');
      return $value === $confirmation;
   }

   protected function validateDate(string $field): bool
   {
      $value = $this->getValue($field);
      return strtotime($value) !== false;
   }
}
