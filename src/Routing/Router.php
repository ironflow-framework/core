<?php

declare(strict_types=1);

namespace IronFlow\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Core\Container\ContainerInterface;
use App\Controllers\AuthController;
use IronFlow\Core\Exceptions\HttpException;

/**
 * Gestionnaire de routage
 * 
 * Cette classe gère le routage des requêtes HTTP dans l'application.
 * Elle permet de définir des routes, de les grouper, et de les faire correspondre
 * aux requêtes entrantes.
 */
class Router implements RouterInterface
{
   /**
    * La collection de routes
    */
   private RouteCollection $routes;

   /**
    * Les middlewares globaux
    * 
    * @var array<string>
    */
   private array $middleware = [];

   /**
    * Les routes nommées
    * 
    * @var array<string, Route>
    */
   private array $namedRoutes = [];

   /**
    * Le préfixe du groupe de routes actuel
    */
   private ?string $currentGroupPrefix = null;

   /**
    * La dernière route ajoutée
    */
   private ?Route $lastRoute = null;

   /**
    * Le conteneur d'injection de dépendances
    */
   private ContainerInterface $container;

   /**
    * Crée une nouvelle instance du routeur
    * 
    * @param ContainerInterface $container Le conteneur d'injection de dépendances
    */
   public function __construct(ContainerInterface $container)
   {
      $this->container = $container;
      $this->routes = new RouteCollection();
   }

   /**
    * Ajoute une route
    * 
    * @param string $method La méthode HTTP
    * @param string $path L'URI de la route
    * @param mixed $handler L'action à exécuter
    * @return Route La route créée
    */
   public function addRoute(string $method, string $path, mixed $handler): Route
   {
      $route = new Route(
         $path,
         ['_controller' => $handler],
         [],
         [],
         '',
         [],
         [$method]
      );

      if ($this->currentGroupPrefix !== null) {
         $route->setPath($this->normalizePath($this->currentGroupPrefix . '/' . ltrim($path, '/')));
      }

      $this->routes->add($method . '_' . $path, $route);
      $this->lastRoute = $route;

      return $route;
   }

   /**
    * Normalise un chemin d'URL en supprimant les barres obliques redondantes
    * 
    * @param string $path Le chemin à normaliser
    * @return string Le chemin normalisé
    */
   private function normalizePath(string $path): string
   {
      // Remplace les séquences multiples de "/" par un seul "/"
      $path = preg_replace('#/+#', '/', $path);
      
      // Supprime le "/" trailing s'il existe, sauf si c'est le seul caractère
      return $path !== '/' ? rtrim($path, '/') : $path;
   }

   /**
    * Ajoute une route GET
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function get(string $uri, array|callable $action): self
   {
      $this->addRoute('GET', $uri, $action);
      return $this;
   }

   /**
    * Ajoute une route POST
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function post(string $uri, array|callable $action): self
   {
      $this->addRoute('POST', $uri, $action);
      return $this;
   }

   /**
    * Ajoute une route PUT
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function put(string $uri, array|callable $action): self
   {
      $this->addRoute('PUT', $uri, $action);
      return $this;
   }

   /**
    * Ajoute une route DELETE
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function delete(string $uri, array|callable $action): self
   {
      $this->addRoute('DELETE', $uri, $action);
      return $this;
   }

   /**
    * Ajoute une route PATCH
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function patch(string $uri, array|callable $action): self
   {
      $this->addRoute('PATCH', $uri, $action);
      return $this;
   }

   /**
    * Ajoute une route OPTIONS
    * 
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function options(string $uri, array|callable $action): self
   {
      $this->addRoute('OPTIONS', $uri, $action);
      return $this;
   }

   /**
    * Ajoute un groupe de routes
    * 
    * @param string $prefix Le préfixe des routes
    * @param callable $callback La fonction de callback
    * @param array $attributes Les attributs du groupe
    * @return self
    */
   public function group(string $prefix, callable $callback, array $attributes = []): self
   {
      // Sauvegarde de l'état actuel
      $previousPrefix = $this->currentGroupPrefix;

      // Définition du nouveau préfixe
      if ($previousPrefix !== null) {
         $this->currentGroupPrefix = $this->normalizePath($previousPrefix . '/' . ltrim($prefix, '/'));
      } else {
         $this->currentGroupPrefix = rtrim($prefix, '/');
      }

      // Gestion des middlewares du groupe
      $previousMiddleware = $this->middleware;
      $this->middleware = array_merge($this->middleware, $attributes['middleware'] ?? []);

      // Exécution du callback avec le nouveau contexte
      $callback($this);

      // Restauration de l'état précédent
      $this->currentGroupPrefix = $previousPrefix;
      $this->middleware = $previousMiddleware;

      return $this;
   }

   /**
    * Ajoute des routes pour plusieurs méthodes HTTP
    * 
    * @param array<string> $methods Les méthodes HTTP
    * @param string $uri L'URI de la route
    * @param array|callable $action L'action à exécuter
    * @return self
    */
   public function match(array $methods, string $uri, array|callable $action): self
   {
      foreach ($methods as $method) {
         $this->addRoute(strtoupper($method), $uri, $action);
      }
      return $this;
   }

   /**
    * Ajoute les routes d'authentification par défaut
    * 
    * @return self
    */
   public function auth(): self
   {
      $this->get('/login', [AuthController::class, 'showLoginForm'])->name('login');
      $this->post('/login', [AuthController::class, 'login']);
      $this->post('/logout', [AuthController::class, 'logout'])->name('logout');
      $this->get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
      $this->post('/register', [AuthController::class, 'register']);
      $this->get('/password/reset', [AuthController::class, 'showResetForm'])->name('password.request');
      $this->post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
      $this->get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
      $this->post('/password/reset', [AuthController::class, 'reset'])->name('password.update');
      
      return $this;
   }

   /**
    * Ajoute les routes RESTful pour une ressource
    * 
    * @param string $name Le nom de la ressource ou l'URI de base de la resource
    * @param string $controller Le contrôleur à utiliser
    * @return self
    */
   public function resource(string $name, string $controller): self
   {
      // Normaliser le nom de ressource
      $basePath = $this->normalizePath($name);
      
      // Route index (liste)
      $this->get($basePath, [$controller, 'index'])->name($name . '.index');

      // Route create (formulaire de création)
      $this->get($basePath . '/create', [$controller, 'create'])->name($name . '.create');

      // Route store (stockage)
      $this->post($basePath, [$controller, 'store'])->name($name . '.store');

      // Route show (affichage)
      $this->get($basePath . '/{id}', [$controller, 'show'])->name($name . '.show');

      // Route edit (formulaire de modification)
      $this->get($basePath . '/{id}/edit', [$controller, 'edit'])->name($name . '.edit');

      // Route update
      $this->put($basePath . '/{id}', [$controller, 'update'])->name($name . '.update');
      // Support pour les formulaires qui ne peuvent pas envoyer PUT directement
      $this->patch($basePath . '/{id}', [$controller, 'update'])->name($name . '.update.patch');

      // Route destroy
      $this->delete($basePath . '/{id}', [$controller, 'destroy'])->name($name . '.destroy');

      return $this;
   }

   /**
    * Ajoute un préfixe à la dernière route
    * 
    * @param string $prefix Le préfixe à ajouter
    * @return self
    */
   public function prefix(string $prefix): self
   {
      if ($this->lastRoute) {
         $currentPath = $this->lastRoute->getPath();
         $this->lastRoute->setPath($this->normalizePath($prefix . '/' . ltrim($currentPath, '/')));
      }
      return $this;
   }

   /**
    * Nomme la dernière route
    * 
    * @param string $name Le nom de la route
    * @return self
    */
   public function name(string $name): self
   {
      if ($this->lastRoute) {
         $this->namedRoutes[$name] = $this->lastRoute;
         $this->lastRoute->setDefault('_name', $name);
      }
      return $this;
   }

   /**
    * Ajoute des middlewares à la dernière route
    * 
    * @param string|array<string> $middleware Les middlewares à ajouter
    * @return self
    */
   public function middleware(string|array $middleware): self
   {
      if ($this->lastRoute) {
         $currentMiddleware = $this->lastRoute->getDefault('_middleware') ?? [];
         $newMiddleware = is_array($middleware) ? $middleware : [$middleware];
         $this->lastRoute->setDefault('_middleware', array_merge($currentMiddleware, $newMiddleware));
      }
      return $this;
   }

   /**
    * Récupère la collection de routes
    * 
    * @return RouteCollection
    */
   public function getRoutes(): RouteCollection
   {
      return $this->routes;
   }

   /**
    * Récupère une route par son nom
    * 
    * @param string $name Le nom de la route
    * @return Route
    * @throws HttpException Si la route n'existe pas
    */
   public function getRoute(string $name): Route
   {
      if (!isset($this->namedRoutes[$name])) {
         throw new HttpException(404, "Route [{$name}] not defined.");
      }
      return $this->namedRoutes[$name];
   }

   /**
    * Génère une URL pour une route nommée
    * 
    * @param string $name Le nom de la route
    * @param array<string, mixed> $parameters Les paramètres de la route
    * @return string L'URL générée
    */
   public function url(string $name, array $parameters = []): string
   {
      return $this->generateUrl($name, $parameters);
   }

   /**
    * Traite une requête et retourne une réponse
    * 
    * @param Request $request La requête à traiter
    * @return Response $response La réponse générée
    * @throws HttpException Si la route n'est pas trouvée
    */
   public function dispatch(Request $request): Response
   {
      $path = $request->getPathInfo();
      $method = $request->getMethod();
      
      // Normaliser le chemin de la requête
      $path = $this->normalizePath($path);

      // Tableau pour stocker les routes correspondantes par leur score de correspondance
      $matchingRoutes = [];
      
      foreach ($this->routes as $name => $route) {
         // Vérifier si la méthode HTTP correspond
         if (!in_array($method, $route->getMethods(), true)) {
            continue;
         }
         
         // Convertir le pattern de la route en expression régulière
         $routePath = $route->getPath();
         $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
         $pattern = '#^' . $pattern . '$#';

         if (preg_match($pattern, $path, $matches)) {
            // Extraire les paramètres de l'URL
            $params = array_filter($matches, function ($key) {
               return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            
            // Calculer un score de correspondance (plus le chemin est spécifique, plus le score est élevé)
            $score = count(explode('/', trim($routePath, '/')));
            
            // Stocker la route et ses paramètres
            $matchingRoutes[] = [
                'score' => $score,
                'route' => $route,
                'params' => $params
            ];
         }
      }
      
      // Si aucune route ne correspond, lancer une exception
      if (empty($matchingRoutes)) {
         throw new HttpException(404, "Route non trouvée : {$path}");
      }
      
      // Trier les routes par score (de la plus spécifique à la moins spécifique)
      usort($matchingRoutes, function($a, $b) {
         return $b['score'] - $a['score'];
      });
      
      // Prendre la route la plus spécifique
      $bestMatch = $matchingRoutes[0];
      $route = $bestMatch['route'];
      $params = $bestMatch['params'];
      
      // Ajouter les paramètres à la requête
      foreach ($params as $key => $value) {
         $request->attributes->set((string) $key, $value);
      }
      
      // Exécuter les middlewares de la route
      $middlewares = $route->getDefault('_middleware') ?? [];
      // TODO: Implémenter l'exécution des middlewares
      
      // Exécuter le contrôleur
      $controller = $route->getDefault('_controller');
      
      if (is_array($controller)) {
         [$class, $method] = $controller;
         $instance = $this->container->make($class);
         
         // Récupérer les paramètres comme tableau
         $routeParams = array_values($params);
         
         // Appeler la méthode du contrôleur avec la requête et les paramètres
         return call_user_func_array([$instance, $method], array_merge([$request], $routeParams));
      } elseif (is_callable($controller)) {
         return $controller($request, ...(array_values($params)));
      }
      
      throw new HttpException(500, "Controller is not callable");
   }

   /**
    * Génère une URL pour une route nommée
    * 
    * @param string $name Le nom de la route
    * @param array<string, mixed> $parameters Les paramètres de la route
    * @return string L'URL générée
    * @throws HttpException Si la route n'existe pas ou si des paramètres sont manquants
    */
   public function generateUrl(string $name, array $parameters = []): string
   {
      $route = $this->getRoute($name);
      $path = $route->getPath();
      
      // Vérifier les paramètres requis
      preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $path, $requiredParams);
      
      foreach ($requiredParams[1] as $param) {
         if (!isset($parameters[$param])) {
            throw new HttpException(500, "Missing parameter [{$param}] for route [{$name}]");
         }
      }

      // Remplacer les paramètres dans l'URL
      foreach ($parameters as $key => $value) {
         $path = preg_replace('/\{' . preg_quote($key, '/') . '\}/', (string) $value, $path);
      }

      return $path;
   }
}