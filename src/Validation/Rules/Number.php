<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour les nombres
 */
class Number extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être un nombre valide';

   /**
    * Valeur minimale
    */
   private ?float $min = null;

   /**
    * Valeur maximale
    */
   private ?float $max = null;

   /**
    * Si seuls les entiers sont acceptés
    */
   private bool $integerOnly = false;

   /**
    * Définit la valeur minimale
    *
    * @param float $min
    * @return self
    */
   public function min(float $min): self
   {
      $this->min = $min;
      $this->setAttribute('min', (string)$min);
      return $this;
   }

   /**
    * Définit la valeur maximale
    *
    * @param float $max
    * @return self
    */
   public function max(float $max): self
   {
      $this->max = $max;
      $this->setAttribute('max', (string)$max);
      return $this;
   }

   /**
    * Définit que seuls les entiers sont acceptés
    *
    * @param bool $integerOnly
    * @return self
    */
   public function integerOnly(bool $integerOnly = true): self
   {
      $this->integerOnly = $integerOnly;
      if ($integerOnly) {
         $this->defaultMessage = 'Le champ :field doit être un nombre entier';
      }
      return $this;
   }

   /**
    * Valide un nombre
    *
    * @param mixed $value
    * @param array $data
    * @return bool
    */
   public function validate($value, array $data = []): bool
   {
      if (empty($value) && $value !== '0' && $value !== 0) {
         return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
      }

      // Vérifier si c'est un nombre
      if (!is_numeric($value)) {
         return false;
      }

      // Convertir en nombre
      $numValue = (float)$value;

      // Vérifier si c'est un entier si nécessaire
      if ($this->integerOnly && floor($numValue) != $numValue) {
         return false;
      }

      // Vérifier min/max
      if ($this->min !== null && $numValue < $this->min) {
         $this->defaultMessage = 'Le champ :field doit être supérieur ou égal à :min';
         return false;
      }

      if ($this->max !== null && $numValue > $this->max) {
         $this->defaultMessage = 'Le champ :field doit être inférieur ou égal à :max';
         return false;
      }

      return true;
   }
}
