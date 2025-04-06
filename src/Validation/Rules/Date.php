<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use DateTime;
use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour les dates
 */
class Date extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être une date valide';

   /**
    * Format de date attendu
    */
   private string $format;

   /**
    * Date minimale
    */
   private ?string $min = null;

   /**
    * Date maximale
    */
   private ?string $max = null;

   /**
    * Autoriser les heures, minutes, secondes
    */
   private bool $allowTime = true;

   /**
    * Constructeur
    * 
    * @param string $format Format de date attendu
    */
   public function __construct(string $format = 'Y-m-d')
   {
      $this->format = $format;
   }

   /**
    * Définit le format de date attendu
    * 
    * @param string $format
    * @return self
    */
   public function format(string $format): self
   {
      $this->format = $format;
      return $this;
   }

   /**
    * Définit la date minimale
    * 
    * @param string $min
    * @return self
    */
   public function min(string $min): self
   {
      $this->min = $min;
      $this->setAttribute('min', $min);
      return $this;
   }

   /**
    * Définit la date maximale
    * 
    * @param string $max
    * @return self
    */
   public function max(string $max): self
   {
      $this->max = $max;
      $this->setAttribute('max', $max);
      return $this;
   }

   /**
    * Autorise ou non l'heure dans la date
    * 
    * @param bool $allowTime
    * @return self
    */
   public function allowTime(bool $allowTime = true): self
   {
      $this->allowTime = $allowTime;
      return $this;
   }

   /**
    * Valide une date
    * 
    * @param mixed $value
    * @param array $data
    * @return bool
    */
    public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool
    {
      if (empty($value)) {
         return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
      }

      // Création de la date selon le format
      $date = DateTime::createFromFormat($this->format, $value);
      if ($date === false) {
         return false;
      }

      // Vérification des erreurs de parsing
      $errors = DateTime::getLastErrors();
      if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
         return false;
      }

      // Vérification du temps si non autorisé
      if (!$this->allowTime && $date->format('H:i:s') !== '00:00:00') {
         return false;
      }

      // Vérification de la date minimale
      if ($this->min !== null) {
         $minDate = DateTime::createFromFormat($this->format, $this->min);
         if ($minDate && $date < $minDate) {
            return false;
         }
      }

      // Vérification de la date maximale
      if ($this->max !== null) {
         $maxDate = DateTime::createFromFormat($this->format, $this->max);
         if ($maxDate && $date > $maxDate) {
            return false;
         }
      }

      return true;
   }
}
