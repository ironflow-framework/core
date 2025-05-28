<?php

declare(strict_types=1);

namespace IronFlow\Http\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception pour les erreurs HTTP
 * 
 * Cette classe représente les erreurs liées aux requêtes HTTP,
 * comme les erreurs 404, 403, 500, etc.
 */
class HttpException extends ApplicationException
{
   /**
    * Crée une nouvelle instance de l'exception HTTP
    * 
    * @param int $statusCode Le code de statut HTTP
    * @param string $message Le message d'erreur
    * @param array<string, mixed> $context Les données contextuelles
    * @param \Throwable|null $previous L'exception précédente
    */
   public function __construct(
      int $statusCode,
      string $message = "",
      array $context = [],
      ?\Throwable $previous = null
   ) {
      parent::__construct($message, $context, $statusCode, $previous);
   }

   /**
    * Récupère le code de statut HTTP
    * 
    * @return int Le code de statut HTTP
    */
   public function getStatusCode(): int
   {
      return $this->getCode();
   }
}
