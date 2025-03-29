<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour la longueur des chaînes
 */
class StringLength extends AbstractRule
{
   /**
    * Longueur minimale
    */
   private ?int $min;

   /**
    * Longueur maximale
    */
   private ?int $max;

   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit contenir entre :min et :max caractères';

   /**
    * Constructeur
    *
    * @param int|null $min Longueur minimale
    * @param int|null $max Longueur maximale
    */
   public function __construct(?int $min = null, ?int $max = null)
   {
      $this->min = $min;
      $this->max = $max;

      if ($min !== null && $max === null) {
         $this->defaultMessage = 'Le champ :field doit contenir au moins :min caractères';
         $this->setAttribute('min', (string)$min);
      } elseif ($min === null && $max !== null) {
         $this->defaultMessage = 'Le champ :field ne doit pas dépasser :max caractères';
         $this->setAttribute('max', (string)$max);
      } else {
         $this->setAttribute('min', (string)$min);
         $this->setAttribute('max', (string)$max);
      }
   }

   /**
    * Définit la longueur minimale
    *
    * @param int $min
    * @return self
    */
   public function min(int $min): self
   {
      $this->min = $min;
      $this->setAttribute('min', (string)$min);
      return $this;
   }

   /**
    * Définit la longueur maximale
    *
    * @param int $max
    * @return self
    */
   public function max(int $max): self
   {
      $this->max = $max;
      $this->setAttribute('max', (string)$max);
      return $this;
   }

   /**
    * Valide la longueur d'une chaîne
    *
    * @param mixed $value
    * @param array $data
    * @return bool
    */
   public function validate($value, array $data = []): bool
   {
      if (empty($value) && $value !== '0') {
         return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
      }

      $length = mb_strlen((string)$value, 'UTF-8');

      if ($this->min !== null && $length < $this->min) {
         return false;
      }

      if ($this->max !== null && $length > $this->max) {
         return false;
      }

      return true;
   }
}
