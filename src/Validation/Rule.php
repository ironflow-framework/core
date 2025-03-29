<?php

declare(strict_types=1);

namespace IronFlow\Validation;

/**
 * Interface pour les règles de validation
 */
interface Rule
{
   /**
    * Valide la valeur selon la règle
    *
    * @param mixed $value Valeur à valider
    * @param array $data Données complètes du formulaire
    * @return bool True si la validation réussit, false sinon
    */
   public function validate($value, array $data = []): bool;

   /**
    * Récupère le message d'erreur pour cette règle
    *
    * @return string
    */
   public function getMessage(): string;

   /**
    * Définit le message d'erreur pour cette règle
    *
    * @param string $message
    * @return self
    */
   public function setMessage(string $message): self;
}
