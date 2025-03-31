<?php

declare(strict_types=1);

namespace IronFlow\Validation;

/**
 * Interface pour les règles de validation
 */
interface Rule
{
   /**
    * Valide une valeur
    *
    * @param string $field Nom du champ
    * @param mixed $value Valeur à valider
    * @param array $parameters Paramètres de la règle
    * @param array $data Données complètes du formulaire
    * @return bool
    */
   public function validate(string $field, $value, array $parameters = [], array $data = []): bool;

   /**
    * Récupère le message d'erreur
    *
    * @return string
    */
   public function getMessage(): string;

   /**
    * Définit le message d'erreur
    *
    * @param string $message
    * @return self
    */
   public function setMessage(string $message): self;

   /**
    * Récupère les paramètres de la règle
    *
    * @return array
    */
   public function getParameters(): array;

   /**
    * Définit les paramètres de la règle
    *
    * @param array $parameters
    * @return self
    */
   public function setParameters(array $parameters): self;
}
