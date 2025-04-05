<?php

namespace IronFlow\Http\Exceptions;

use Exception;

class HttpException extends Exception
{
   protected int $statusCode = 500;

   public function getStatusCode(): int
   {
      return $this->statusCode;
   }
}
