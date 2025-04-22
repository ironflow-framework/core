<?php

declare(strict_types=1);

namespace IronFlow\Session\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception levée lors d'une erreur de session
 */
class SessionException extends ApplicationException
{
   /**
    * Constructeur de l'exception
    *
    * @param string $message Message d'erreur
    * @param array<string, mixed> $context Données contextuelles
    * @param \Throwable|null $previous Exception précédente
    */
   public function __construct(
      string $message = "Erreur de session",
      array $context = [],
      ?\Throwable $previous = null
   ) {
      parent::__construct($message, $context, $previous);
   }
}
