<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour les champs nullables
 */
class Nullable extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field est invalide';

   /**
    * Valide si la valeur est nullable
    *
    * @param string $field
    * @param mixed $value
    * @param array $parameters
    * @param array $data
    * @return bool
    */
   public function validate(string $field, $value, array $parameters = [], array $data = []): bool
   {
      return true; // Un champ nullable est toujours valide
   }
}
