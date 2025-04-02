<?php

declare(strict_types=1);

namespace IronFlow\Http\Exceptions;

class RateLimitException extends \Exception
{
   /**
    * Constructeur
    */
   public function __construct(string $message = 'Trop de requêtes.', int $code = 429)
   {
      parent::__construct($message, $code);
   }
}
