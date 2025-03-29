<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour les adresses email
 */
class Email extends AbstractRule
{
   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'Le champ :field doit être une adresse email valide';

   /**
    * Vérification DNS
    */
   private bool $checkDns;

   /**
    * Constructeur
    *
    * @param bool $checkDns Vérifie l'existence du domaine via DNS
    */
   public function __construct(bool $checkDns = false)
   {
      $this->checkDns = $checkDns;
   }

   /**
    * Valide une adresse email
    *
    * @param mixed $value
    * @param array $data
    * @return bool
    */
   public function validate($value, array $data = []): bool
   {
      if (empty($value)) {
         return true; // Pas d'erreur si vide (la règle Required doit être utilisée pour vérifier la présence)
      }

      if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
         return false;
      }

      if ($this->checkDns) {
         $domain = substr(strrchr($value, "@"), 1);
         if (!$domain || !checkdnsrr($domain, 'MX')) {
            return false;
         }
      }

      return true;
   }
}
