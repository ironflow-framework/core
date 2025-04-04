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

      // Vérifier si c'est un nombre
      if (!is_numeric($value)) {
         return false;
      }

      $number = (float)$value;

      // Vérifier si c'est un entier si requis
      if ($this->integerOnly && !is_int($number)) {
         return false;
      }

      // Vérifier la valeur minimale
      if ($this->min !== null && $number < $this->min) {
         return false;
      }

      // Vérifier la valeur maximale
      if ($this->max !== null && $number > $this->max) {
         return false;
      }

      return true;
   }
}
