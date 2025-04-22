<?php

declare(strict_types=1);

namespace IronFlow\Core\Event\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception pour les erreurs liées aux événements
 */
class EventException extends ApplicationException
{
   /**
    * Constructeur
    *
    * @param string $message
    * @param int $code
    * @param \Throwable|null $previous
    */
   public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
   {
      parent::__construct($message, $code, $previous);
   }
}
