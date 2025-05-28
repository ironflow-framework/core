<?php

declare(strict_types=1);

namespace IronFlow\Http\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception pour les CsrfToken
 * 
 * Cette classe represente les erreurs liées aux CSRF
 */
class CsrfTokenException extends ApplicationException
{
   /**
    * Crée une nouvelle instance de l'exception CSRF
    */
   public function __construct(string $message = "Token CSRF invalide.", int $code = 419)
   {
      parent::__construct($message, [], $code);
   }
}
