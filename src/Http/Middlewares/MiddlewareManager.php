<?php

declare(strict_types=1);

namespace IronFlow\Http\Middlewares;

use IronFlow\Core\Contracts\ContainerInterface;
use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

/**
 * Classe MiddlewareManager
 * 
 * Gère la chaîne de traitement des middlewares
 */
class MiddlewareManager
{
   /**
    * @var ContainerInterface
    */
   private ContainerInterface $container;

   /**
    * @var array<string, string>
    */
   private array $middleware = [];

   /**
    * Constructeur
    * 
    * @param ContainerInterface $container
    */
   public function __construct(ContainerInterface $container)
   {
      $this->container = $container;
   }

   /**
    * Ajoute un middleware à la chaîne
    * 
    * @param string $middleware
    * @return self
    */
   public function add(string $middleware): self
   {
      $this->middleware[] = $middleware;
      return $this;
   }

   /**
    * Traite la requête à travers tous les middlewares
    * 
    * @param Request $request
    * @param callable $handler
    * @return Response
    */
   public function run(Request $request, callable $handler): Response
   {
      $next = $handler;

      foreach (array_reverse($this->middleware) as $middleware) {
         $instance = $this->container->make($middleware);
         if ($instance instanceof MiddlewareInterface) {
            $next = fn(Request $request) => $instance->handle($request, $next);
         }
      }

      return $next($request);
   }

   /**
    * Supprime un middleware
    * 
    * @param string $name
    * @return void
    */
   public function remove(string $name): void
   {
      unset($this->middleware[$name]);
   }

   /**
    * Vérifie si un middleware existe
    * 
    * @param string $name
    * @return bool
    */
   public function has(string $name): bool
   {
      return isset($this->middleware[$name]);
   }
}
