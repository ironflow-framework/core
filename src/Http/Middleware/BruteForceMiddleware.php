<?php

declare(strict_types=1);

namespace IronFlow\Http\Middleware;

use IronFlow\Http\Request;
use IronFlow\Http\Exceptions\TooManyRequestsException;
use IronFlow\Support\Facades\Cache;
use IronFlow\Support\Facades\Security;

class BruteForceMiddleware
{
   /**
    * Nombre maximum de tentatives
    */
   private const MAX_ATTEMPTS = 5;

   /**
    * Durée du blocage en minutes
    */
   private const BLOCK_DURATION = 15;

   /**
    * Gère la requête et vérifie les tentatives de connexion
    */
   public function handle(Request $request, callable $next)
   {
      $key = $this->getKey($request);

      if ($this->isBlocked($key)) {
         Security::logBruteForceAttempt([
            'ip' => $request->ip(),
            'path' => $request->getPathInfo(),
            'attempts' => Cache::get($key)
         ]);

         throw new TooManyRequestsException(
            'Trop de tentatives. Veuillez réessayer dans ' .
               self::BLOCK_DURATION . ' minutes.'
         );
      }

      $response = $next($request);

      if ($this->isFailedAttempt($response)) {
         $this->incrementAttempts($key);
      } else {
         $this->resetAttempts($key);
      }

      return $response;
   }

   /**
    * Génère une clé unique pour l'IP et le chemin
    */
   protected function getKey(Request $request): string
   {
      return 'brute_force:' . $request->ip() . ':' . $request->getPathInfo();
   }

   /**
    * Vérifie si l'IP est bloquée
    */
   protected function isBlocked(string $key): bool
   {
      $attempts = Cache::get($key, 0);
      return $attempts >= self::MAX_ATTEMPTS;
   }

   /**
    * Incrémente le compteur de tentatives
    */
   protected function incrementAttempts(string $key): void
   {
      $attempts = Cache::get($key, 0) + 1;
      Cache::put($key, $attempts, self::BLOCK_DURATION * 60);
   }

   /**
    * Réinitialise le compteur de tentatives
    */
   protected function resetAttempts(string $key): void
   {
      Cache::forget($key);
   }

   /**
    * Vérifie si la tentative a échoué
    */
   protected function isFailedAttempt($response): bool
   {
      return $response->getStatusCode() >= 400;
   }
}
