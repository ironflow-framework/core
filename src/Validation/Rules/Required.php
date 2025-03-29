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
   protected string $defaultMessage = 'Le champ :field est obligatoire';

   /**
    * Vérifie si un champ est présent et non vide
    *
    * @param mixed $value
    * @param array $data
    * @return bool
    */
   public function validate($value, array $data = []): bool
   {
      // Cas spécial pour "0" qui est considéré vide par empty() mais valide pour nous
      if ($value === 0 || $value === '0' || $value === false) {
         return true;
      }

      // Tableaux vides non autorisés
      if (is_array($value) && count($value) === 0) {
         return false;
      }

      // Valider que le champ est présent et non vide
      return !empty($value);
   }
}
