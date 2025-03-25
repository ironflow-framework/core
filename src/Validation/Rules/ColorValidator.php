<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\Validator;

class ColorValidator extends Validator
{
   protected array $allowedColors = [];
   protected bool $allowAlpha = false;
   protected bool $required = false;

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function allowedColors(array $colors): self
   {
      $this->allowedColors = $colors;
      return $this;
   }

   public function allowAlpha(bool $allow = true): self
   {
      $this->allowAlpha = $allow;
      return $this;
   }

   public function validate($value, array $data = []): bool
   {
      if (empty($value)) {
         return !$this->required;
      }

      if (!$this->isValidColorFormat($value)) {
         $this->addError('La couleur n\'est pas dans un format valide.', 'format');
         return false;
      }

      if (!empty($this->allowedColors) && !in_array($value, $this->allowedColors)) {
         $this->addError('La couleur n\'est pas dans la liste des couleurs autorisées.', 'allowed');
         return false;
      }

      return true;
   }

   protected function isValidColorFormat(string $color): bool
   {
      // Format hexadécimal (3 ou 6 caractères)
      if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
         return true;
      }

      // Format RGB
      if (preg_match('/^rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)$/', $color)) {
         return true;
      }

      // Format RGBA (si autorisé)
      if ($this->allowAlpha && preg_match('/^rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*[0-1]\.?[0-9]*\)$/', $color)) {
         return true;
      }

      // Noms de couleurs CSS prédéfinis
      $cssColors = [
         'black',
         'silver',
         'gray',
         'white',
         'maroon',
         'red',
         'purple',
         'fuchsia',
         'green',
         'lime',
         'olive',
         'yellow',
         'navy',
         'blue',
         'teal',
         'aqua'
      ];

      return in_array(strtolower($color), $cssColors);
   }
}
