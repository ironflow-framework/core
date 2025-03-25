<?php

namespace IronFlow\Http\Exceptions;

class NotFoundException extends \Exception
{
   protected $code = 404;

   public function getStatusCode(): int
   {
      return $this->code;
   }
}
