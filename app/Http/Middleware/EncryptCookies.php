<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
   /**
    * Les noms des cookies qui ne doivent pas être encryptés.
    *
    * @var array<int, string>
    */
   protected $except = [
      //
   ];
}
