<?php

namespace IronFlow\Http\Middlewares;

use IronFlow\Http\Contracts\MiddlewareInterface;
use Ironflow\Http\Request;
use Ironflow\Http\Response;

/**
 * Classe pour les Rate Limiting Middleware
 * 
 */
class RateLimitMiddleware implements MiddlewareInterface
{

   /**
    * Configuration du rate limiting
    */
   private array $config = [
      'max_requests' => 60, // Nombre maximum de requêtes
      'time_window' => 60, // Fenêtre de temps en secondes
      'block_duration' => 300, // Durée de blocage en secondes
   ];

   /**
    * Gère la requête
    */
   public function handle(Request $request, callable $next): Response
   {
      $ip = $request->ip();
      $key = "rate_limit_{$ip}";

      // Vérifier si l'IP est bloquée
      if (session()->has("blocked_{$ip}")) {
         $blockedUntil = session()->get("blocked_{$ip}");
         if (time() < $blockedUntil) {
            return Response::json([
               'error' => 'Trop de requêtes. Veuillez réessayer plus tard.'
            ], 429);
         }
         // Débloquer l'IP si le temps de blocage est écoulé
         session()->get("blocked_{$ip}");
      }

      // Récupérer les statistiques de requêtes
      $stats = session()->get($key, [
         'count' => 0,
         'window_start' => time()
      ]);

      // Réinitialiser le compteur si la fenêtre de temps est écoulée
      if (time() - $stats['window_start'] > $this->config['time_window']) {
         $stats = [
            'count' => 0,
            'window_start' => time()
         ];
      }

      // Incrémenter le compteur
      $stats['count']++;

      // Sauvegarder les nouvelles statistiques
      session()->set($key, $stats);

      // Vérifier si la limite est dépassée. Si Oui bloquer l'IP
      if ($stats['count'] > $this->config['max_requests']) {
         session()->put("blocked_{$ip}", time() + $this->config['block_duration']);
         return Response::json([
            'error' => 'Trop de requêtes. Veuillez réessayer plus tard.'
         ], 429);
      }

      return $next($request);
   }

   /**
    * Configure le rate limiting
    */
   public function configure(array $config): self
   {
      $this->config = array_merge($this->config, $config);
      return $this;
   }

   /**
    * Réinitialise le compteur pour une IP
    */
   public function reset(string $ip): void
   {
      session()->remove("rate_limit_{$ip}");
      session()->remove("blocked_{$ip}");
   }
}
