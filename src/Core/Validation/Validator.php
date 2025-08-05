<?php

namespace IronFlow\Core\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use InvalidArgumentException;

class Validator
{
    protected array $rules = [];
    protected array $errors = [];
    protected array $customMessages = [];
    protected bool $stopOnFirstFailure = false;

    /**
     * Définit les règles de validation
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Ajoute une règle de validation pour un champ spécifique
     */
    public function addRule(string $field, $rule): self
    {
        $this->rules[$field] = $rule;
        return $this;
    }

    /**
     * Définit des messages d'erreur personnalisés
     */
    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Configure si la validation doit s'arrêter au premier échec
     */
    public function stopOnFirstFailure(bool $stop = true): self
    {
        $this->stopOnFirstFailure = $stop;
        return $this;
    }

    /**
     * Valide les données selon les règles définies
     */
    public function validate(array $data): bool
    {
        if (empty($this->rules)) {
            throw new InvalidArgumentException('Aucune règle de validation définie');
        }

        $this->errors = [];
        
        foreach ($this->rules as $field => $rule) {
            try {
                // Validation du champ
                $this->validateField($field, $rule, $data);
                
                // Arrêt si configuré et des erreurs existent
                if ($this->stopOnFirstFailure && !empty($this->errors)) {
                    break;
                }
            } catch (NestedValidationException $e) {
                $this->handleValidationException($field, $e);
            }
        }
        
        return empty($this->errors);
    }

    /**
     * Valide un seul champ
     */
    public function validateField(string $field, $rule, array $data): bool
    {
        $validator = v::create()->key($field, $rule, false);
        
        try {
            $validator->assert($data);
            return true;
        } catch (NestedValidationException $e) {
            $this->handleValidationException($field, $e);
            return false;
        }
    }

    /**
     * Valide une valeur directement (sans structure de données)
     */
    public function validateValue($value, $rule, string $fieldName = 'value'): bool
    {
        try {
            $rule->assert($value);
            return true;
        } catch (NestedValidationException $e) {
            $this->handleValidationException($fieldName, $e);
            return false;
        }
    }

    /**
     * Gère les exceptions de validation
     */
    protected function handleValidationException(string $field, NestedValidationException $e): void
    {
        $messages = $e->getMessages();
        
        // Application des messages personnalisés si définis
        if (isset($this->customMessages[$field])) {
            $customMessage = $this->customMessages[$field];
            if (is_string($customMessage)) {
                $messages = [$customMessage];
            } elseif (is_array($customMessage)) {
                $messages = array_merge($messages, $customMessage);
            }
        }
        
        $this->errors[$field] = $messages;
    }

    /**
     * Retourne toutes les erreurs
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Retourne les erreurs pour un champ spécifique
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Vérifie si un champ a des erreurs
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Retourne le premier message d'erreur pour un champ
     */
    public function getFirstFieldError(string $field): ?string
    {
        $errors = $this->getFieldErrors($field);
        return !empty($errors) ? reset($errors) : null;
    }

    /**
     * Retourne tous les messages d'erreur sous forme de tableau plat
     */
    public function getFlatErrors(): array
    {
        $flatErrors = [];
        foreach ($this->errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $flatErrors[] = $error;
            }
        }
        return $flatErrors;
    }

    /**
     * Retourne le nombre total d'erreurs
     */
    public function getErrorCount(): int
    {
        return array_sum(array_map('count', $this->errors));
    }

    /**
     * Remet à zéro les erreurs
     */
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Remet à zéro les règles
     */
    public function clearRules(): self
    {
        $this->rules = [];
        return $this;
    }

    /**
     * Retourne les règles actuelles
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Vérifie si des règles sont définies
     */
    public function hasRules(): bool
    {
        return !empty($this->rules);
    }
}