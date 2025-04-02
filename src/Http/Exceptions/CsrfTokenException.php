<?php

declare(strict_types=1);

namespace IronFlow\Http\Exceptions;

class CsrfTokenException extends \Exception
{
   /**
    * Crée une nouvelle instance de l'exception CSRF
    */
   public function __construct(string $message = "Token CSRF invalide.", int $code = 419)
   {
      parent::__construct($message, $code);
   }
}
