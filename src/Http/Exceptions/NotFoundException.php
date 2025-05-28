<?php

namespace IronFlow\Http\Exceptions;

/**
 * Exception pour les erreurs 404
 * 
 * Cette classe represente les erreurs liées aux requêtes Http 404
 */
class NotFoundException extends HttpException
{

   public function __construct($message = "Not Found") {
      parent::__construct(404, $message);

      $this->code = 404;
   }

   /**
    * Recupère le status du code de l'erreur
    * @return int
    */
   public function getStatusCode(): int
   {
      return $this->code;
   }
}
