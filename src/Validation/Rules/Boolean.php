<?php

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

class Boolean extends AbstractRule
{
    /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être soit une valeur true soit une valeur false.';

    /**
    * Valide une valeur
    */
   public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool
   {
    return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
   }
}