<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour champ obligatoire
 */
class Required extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field est requis.';

   /**
    * Valide une valeur
    */
   public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool
   {
      if (empty($value) && $value !== '0' && $value !== 0) {
         $this->setAttribute('field', $field);
         return false;
      }

      return true;
   }
}
