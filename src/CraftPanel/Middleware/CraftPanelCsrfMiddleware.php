<?php

namespace IronFlow\CraftPanel\Middleware;

use IronFlow\Http\Middleware;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class CraftPanelCsrfMiddleware extends Middleware
{
   /**
    * Handle an incoming request.
    *
    * @param  \IronFlow\Http\Request  $request
    * @param  callable  $next
    * @return \IronFlow\Http\Response
    */
   public function handle(Request $request, callable $next): Response
   {
      // Ignorer la vérification pour les requêtes GET, HEAD, OPTIONS
      $skip = in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']);

      if (!$skip && !$this->tokensMatch($request)) {
         return Response::json([
            'error' => 'CSRF token mismatch',
            'message' => 'La session a expiré. Veuillez rafraîchir la page et réessayer.'
         ], 419);
      }

      return $next($request);
   }

   /**
    * Determine if the token matches the request token.
    *
    * @param  \IronFlow\Http\Request  $request
    * @return bool
    */
   protected function tokensMatch(Request $request): bool
   {
      $token = $request->input('_token') ?: $request->headers->get('X-CSRF-TOKEN');

      // Fallback to the header if not found in the request
      if (!$token && $header = $request->headers->get('X-XSRF-TOKEN')) {
         $token = $header;
      }

      return is_string($token) && is_string(session()->get('_token')) && hash_equals(session()->get('_token'), $token);
   }
}
