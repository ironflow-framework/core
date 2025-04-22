<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class StartSession
{
   /**
    * Handle an incoming request.
    *
    * @param  \Symfony\Component\HttpFoundation\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
   public function handle(Request $request, Closure $next)
   {
      if (!$request->hasSession()) {
         $request->setSession(new Session());
      }

      return $next($request);
   }
}
