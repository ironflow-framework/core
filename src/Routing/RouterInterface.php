<?php

declare(strict_types=1);

namespace IronFlow\Routing;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use Symfony\Component\Routing\Route;

interface RouterInterface
{
   /**
    * Dispatch la requête vers le bon contrôleur
    * 
    * @param Request $request La requête à dispatcher
    * @return Response La réponse générée
    */
   public function dispatch(Request $request): Response;

   /**
    * Ajoute une route à la collection
    * 
    * @param string $method La méthode HTTP
    * @param string $path Le chemin de la route
    * @param mixed $handler Le gestionnaire de la route
    * @return Route La route créée
    */
   public function addRoute(string $method, string $path, mixed $handler): Route;

   /**
    * Génère une URL à partir du nom d'une route
    * 
    * @param string $name Le nom de la route
    * @param array $parameters Les paramètres de la route
    * @return string L'URL générée
    */
   public function generateUrl(string $name, array $parameters = []): string;
}
