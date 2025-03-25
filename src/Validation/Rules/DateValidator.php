<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\Validator;

class DateValidator extends Validator
{
   protected ?string $min = null;
   protected ?string $max = null;
   protected string $format = 'Y-m-d';
   protected bool $allowTime = false;
   protected bool $required = false;

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function min(string $date): self
   {
      $this->min = $date;
      return $this;
   }

   public function max(string $date): self
   {
      $this->max = $date;
      return $this;
   }

   public function format(string $format): self
   {
      $this->format = $format;
      return $this;
   }

   public function allowTime(bool $allow = true): self
   {
      $this->allowTime = $allow;
      return $this;
   }

   public function validate($value, array $data = []): bool
   {
      if (empty($value)) {
         return !$this->required;
      }

      $date = \DateTime::createFromFormat($this->format, $value);
      if (!$date) {
         $this->addError('La date n\'est pas dans le bon format.', 'format');
         return false;
      }

      if ($this->min) {
         $minDate = \DateTime::createFromFormat($this->format, $this->min);
         if ($minDate && $date < $minDate) {
            $this->addError(sprintf('La date doit être supérieure à %s.', $this->min), 'min');
            return false;
         }
      }

      if ($this->max) {
         $maxDate = \DateTime::createFromFormat($this->format, $this->max);
         if ($maxDate && $date > $maxDate) {
            $this->addError(sprintf('La date doit être inférieure à %s.', $this->max), 'max');
            return false;
         }
      }

      if (!$this->allowTime && $date->format('H:i:s') !== '00:00:00') {
         $this->addError('La date ne doit pas contenir d\'heure.', 'time');
         return false;
      }

      return true;
   }
}
