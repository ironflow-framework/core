<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

/**
 * Interface pour les règles de validation
 * 
 * Cette interface définit le contrat que toutes les règles de validation
 * doivent respecter. Elle est utilisée par le système de validation pour
 * s'assurer que toutes les règles implémentent les méthodes nécessaires.
 */
interface RuleInterface
{
   /**
    * Valide une valeur
    *
    * @param string $field Nom du champ
    * @param mixed $value Valeur à valider
    * @param array $parameters Paramètres de la règle
    * @param array $data Données complètes du formulaire
    * @return bool True si la validation réussit, false sinon
    */
   public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool;

   /**
    * Récupère le message d'erreur
    *
    * @return string Le message d'erreur formaté
    */
   public function getMessage(): string;

   /**
    * Récupère le nom de la règle
    *
    * @return string Le nom de la règle
    */
   public function getName(): string;

   /**
    * Vérifie si la règle passe
    *
    * @param string $field Nom du champ
    * @param mixed $value Valeur à valider
    * @param array $data Données complètes du formulaire
    * @return bool True si la validation réussit, false sinon
    */
   public function passes(string $field, mixed $value, array $data = []): bool;

   /**
    * Définit le message d'erreur
    *
    * @param string $message Le message d'erreur
    * @return self
    */
   public function setMessage(string $message): self;

   /**
    * Récupère les paramètres de la règle
    *
    * @return array Les paramètres de la règle
    */
   public function getParameters(): array;

   /**
    * Définit les paramètres de la règle
    *
    * @param array $parameters Les paramètres de la règle
    * @return self
    */
   public function setParameters(array $parameters): self;
}
