<?php

declare(strict_types=1);

namespace IronFlow\Http\Middlewares;

use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Http\Exceptions\CsrfTokenException;
use IronFlow\Support\Facades\Session;
use IronFlow\Support\Facades\Security;

class CsrfMiddleware implements MiddlewareInterface
{
   /**
    * Gère la requête et vérifie le token CSRF
    */
   public function handle(Request $request, callable $next): Response
   {
      if ($this->isReading($request)) {
         return $next($request);
      }

      if (!$this->tokensMatch($request)) {
         Security::logCsrfAttempt([
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'token' => $request->input('_token') ?: $request->header('X-CSRF-TOKEN'),
            'expected_token' => Session::get('_token')
         ]);

         throw new CsrfTokenException('Token CSRF invalide.');
      }

      return $next($request);
   }

   /**
    * Vérifie si la requête est en lecture seule
    */
   protected function isReading(Request $request): bool
   {
      return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
   }

   /**
    * Vérifie si les tokens CSRF correspondent
    */
   protected function tokensMatch(Request $request): bool
   {
      $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

      return is_string($token) && hash_equals(session()->get('_token'), $token);
   }
}
