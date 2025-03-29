<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour vérifier si une valeur se trouve dans une liste de valeurs autorisées
 */
class InArray extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être une valeur autorisée';

   /**
    * Liste des valeurs autorisées
    */
   private array $allowedValues;

   /**
    * Vérification stricte des types
    */
   private bool $strict;

   /**
    * Constructeur
    * 
    * @param array $allowedValues Liste des valeurs autorisées
    * @param bool $strict Vérification stricte des types
    */
   public function __construct(array $allowedValues, bool $strict = false)
   {
      $this->allowedValues = $allowedValues;
      $this->strict = $strict;
      $this->setAttribute('allowedValues', implode(', ', $this->formatAllowedValues()));
   }

   /**
    * Définit la liste des valeurs autorisées
    * 
    * @param array $allowedValues
    * @return self
    */
   public function setAllowedValues(array $allowedValues): self
   {
      $this->allowedValues = $allowedValues;
      $this->setAttribute('allowedValues', implode(', ', $this->formatAllowedValues()));
      return $this;
   }

   /**
    * Active ou désactive la vérification stricte des types
    * 
    * @param bool $strict
    * @return self
    */
   public function strict(bool $strict = true): self
   {
      $this->strict = $strict;
      return $this;
   }

   /**
    * Formate les valeurs autorisées pour l'affichage
    * 
    * @return array
    */
   private function formatAllowedValues(): array
   {
      return array_map(function ($value) {
         if (is_bool($value)) {
            return $value ? 'true' : 'false';
         }
         if (is_null($value)) {
            return 'null';
         }
         if (is_array($value)) {
            return 'array';
         }
         if (is_object($value)) {
            return get_class($value);
         }

         return (string) $value;
      }, $this->allowedValues);
   }

   /**
    * Valide si une valeur se trouve dans la liste des valeurs autorisées
    * 
    * @param mixed $value
    * @param array $data
    * @return bool
    */
   public function validate($value, array $data = []): bool
   {
      if ($value === null || $value === '') {
         return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
      }

      return in_array($value, $this->allowedValues, $this->strict);
   }
}
