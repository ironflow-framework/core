<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation par expression régulière
 */
class Regex extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field n\'est pas au format valide';

   /**
    * Expression régulière à utiliser
    */
   private string $pattern;

   /**
    * Constructeur
    *
    * @param string $pattern Expression régulière à utiliser
    */
   public function __construct(string $pattern)
   {
      $this->pattern = $pattern;
   }

   /**
    * Définit l'expression régulière
    *
    * @param string $pattern
    * @return self
    */
   public function pattern(string $pattern): self
   {
      $this->pattern = $pattern;
      return $this;
   }

   /**
    * Valide selon une expression régulière
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

      // Conversion en chaîne
      $value = (string)$value;

      // Validation via l'expression régulière
      return preg_match($this->pattern, $value) === 1;
   }
}
