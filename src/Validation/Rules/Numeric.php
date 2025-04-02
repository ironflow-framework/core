<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

class Numeric extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être un nombre.';

   /**
    * Valide une valeur
    */
   public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool
   {
      if (!is_numeric($value)) {
         $this->setAttribute('field', $field);
         return false;
      }

      return true;
   }
}
