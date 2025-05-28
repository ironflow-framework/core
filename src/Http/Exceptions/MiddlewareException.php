<?php

namespace IronFlow\Http\Exceptions;

/**
 * Exception pour les middlewares
 * 
 * Cette classe represente les erreurs middlewares.
 */
class MiddlewareException extends \Exception
{
   public function __construct(string $message)
   {
      parent::__construct($message);
   }
}
