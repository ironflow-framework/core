<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations\Exceptions;

use Exception;

class MigrationException extends Exception
{
   public static function fileNotFound(string $file): self
   {
      return new self("Le fichier de migration '$file' n'a pas été trouvé.");
   }

   public static function classNotFound(string $class): self
   {
      return new self("La classe de migration '$class' n'a pas été trouvée.");
   }

   public static function invalidMigrationClass(string $class): self
   {
      return new self("La classe '$class' n'est pas une migration valide.");
   }
}
