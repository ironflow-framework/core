<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use Exception;

/**
 * Exception pour encapsuler les erreurs PHP
 */
class ErrorException extends Exception
{
   /**
    * @var int Le niveau de l'erreur
    */
   protected int $severity;

   /**
    * Constructeur
    */
   public function __construct(string $message, int $code = 0, int $severity = 1, string $filename = __FILE__, int $lineno = __LINE__, Exception $previous = null)
   {
      parent::__construct($message, $code, $previous);

      $this->severity = $severity;
      $this->file = $filename;
      $this->line = $lineno;
   }

   /**
    * Récupère le niveau de sévérité de l'erreur
    */
   public function getSeverity(): int
   {
      return $this->severity;
   }

   /**
    * Convertit une erreur PHP en exception
    */
   public static function fromError(array $error): self
   {
      return new self(
         $error['message'] ?? 'Unknown error',
         0,
         $error['type'] ?? E_ERROR,
         $error['file'] ?? '',
         $error['line'] ?? 0
      );
   }
}
