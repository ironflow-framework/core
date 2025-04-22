<?php

declare(strict_types=1);

namespace IronFlow\Core\Config\Exceptions;

use IronFlow\Core\Exceptions\ApplicationException;

/**
 * Exception pour les erreurs de configuration
 */
class ConfigException extends ApplicationException
{
   /**
    * Constructeur
    *
    * @param string $message Message d'erreur
    * @param int $code Code d'erreur
    * @param \Throwable|null $previous Exception précédente
    */
   public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
   {
      parent::__construct($message, $code, $previous);
   }
}
