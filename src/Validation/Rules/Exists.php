<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;
use IronFlow\Support\Facades\DB;

/**
 * Règle de validation pour vérifier l'existence d'une valeur dans une table
 */
class Exists extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'La valeur sélectionnée pour :field est invalide';

   /**
    * Valide l'existence d'une valeur dans une table
    *
    * @param string $field
    * @param mixed $value
    * @param array $parameters
    * @param array $data
    * @return bool
    */
   public function validate(string $field, $value, array $parameters = [], array $data = []): bool
   {
      if (empty($value) && $value !== '0' && $value !== 0) {
         return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
      }

      if (empty($parameters)) {
         throw new \InvalidArgumentException('La règle exists nécessite le nom de la table et de la colonne');
      }

      $table = $parameters[0];
      $column = $parameters[1] ?? 'id';

      $result = DB::table($table)
         ->where($column, $value)
         ->first();

      return $result !== null;
   }
}
