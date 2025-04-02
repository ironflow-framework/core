<?php

declare(strict_types=1);

namespace IronFlow\Http\Exceptions;

class TooManyRequestsException extends \Exception
{
   /**
    * Crée une nouvelle instance de l'exception
    */
   public function __construct(string $message = "Trop de requêtes.", int $code = 429)
   {
      parent::__construct($message, $code);
   }
}
