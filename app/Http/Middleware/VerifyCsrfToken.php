<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken
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
      if ($this->isReading($request) || $this->tokensMatch($request)) {
         return $next($request);
      }

      throw new \RuntimeException('CSRF token mismatch.');
   }

   /**
    * Determine if the HTTP request uses a 'read' verb.
    *
    * @param  \Symfony\Component\HttpFoundation\Request  $request
    * @return bool
    */
   protected function isReading(Request $request)
   {
      return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
   }

   /**
    * Determine if the session and input CSRF tokens match.
    *
    * @param  \Symfony\Component\HttpFoundation\Request  $request
    * @return bool
    */
   protected function tokensMatch(Request $request)
   {
      $token = $request->get('_token') ?: $request->headers->get('X-CSRF-TOKEN');

      if (!$token) {
         return false;
      }

      return hash_equals($request->getSession()->get('_token'), $token);
   }
}
