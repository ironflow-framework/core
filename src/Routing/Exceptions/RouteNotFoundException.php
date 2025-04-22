<?php

declare(strict_types=1);

namespace IronFlow\Routing\Exceptions;

use IronFlow\Core\Exceptions\HttpException;

/**
 * Exception levée lorsqu'une route n'est pas trouvée
 */
class RouteNotFoundException extends HttpException
{
   /**
    * Constructeur de l'exception
    *
    * @param string $message Message d'erreur
    * @param array<string, mixed> $context Données contextuelles
    * @param \Throwable|null $previous Exception précédente
    */
   public function __construct(
      string $message = "Route non trouvée",
      array $context = [],
      ?\Throwable $previous = null
   ) {
      parent::__construct(404, $message, $context, $previous);
   }
}
