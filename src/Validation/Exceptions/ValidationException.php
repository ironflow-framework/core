<?php

declare(strict_types=1);

namespace IronFlow\Validation\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception pour les erreurs de validation
 * 
 * Cette classe représente les erreurs liées à la validation des données,
 * comme les erreurs de formulaire ou les erreurs de validation des modèles.
 */
class ValidationException extends ApplicationException
{
   /**
    * Les erreurs de validation
    * 
    * @var array<string, array<string>>
    */
   protected array $errors = [];

   /**
    * Crée une nouvelle instance de l'exception de validation
    * 
    * @param string $message Le message d'erreur
    * @param array<string, array<string>> $errors Les erreurs de validation
    * @param array<string, mixed> $context Les données contextuelles
    * @param \Throwable|null $previous L'exception précédente
    */
   public function __construct(
      string $message = "Les données fournies sont invalides.",
      array $errors = [],
      array $context = [],
      ?\Throwable $previous = null
   ) {
      parent::__construct($message, $context, 422, $previous);
      $this->errors = $errors;
   }

   /**
    * Récupère les erreurs de validation
    * 
    * @return array<string, array<string>> Les erreurs de validation
    */
   public function getErrors(): array
   {
      return $this->errors;
   }

   /**
    * Récupère les erreurs pour un champ spécifique
    * 
    * @param string $field Le nom du champ
    * @return array<string> Les erreurs du champ
    */
   public function getFieldErrors(string $field): array
   {
      return $this->errors[$field] ?? [];
   }

   /**
    * Vérifie si un champ a des erreurs
    * 
    * @param string $field Le nom du champ
    * @return bool True si le champ a des erreurs
    */
   public function hasFieldErrors(string $field): bool
   {
      return isset($this->errors[$field]) && !empty($this->errors[$field]);
   }
}
