<?php

declare(strict_types=1);

namespace IronFlow\Http\Contracts;

use IronFlow\Http\Request;
use IronFlow\Http\Response;

/**
 * Interface pour les middlewares HTTP
 * 
 * Cette interface définit le contrat standard pour tous les middlewares
 * dans l'application. Elle permet de créer une chaîne de traitement
 * pour les requêtes HTTP.
 */
interface MiddlewareInterface
{
   /**
    * Traite la requête
    * 
    * @param Request $request La requête à traiter
    * @param callable $next Le prochain middleware
    * @return Response La réponse générée
    */
   public function handle(Request $request, callable $next): Response;
}
