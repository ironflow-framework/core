<?php

namespace IronFlow\Http\Exceptions;

abstract class HttpException extends \Exception
{
   protected int $statusCode = 500;

   public function getStatusCode(): int
   {
      return $this->statusCode;
   }
}
