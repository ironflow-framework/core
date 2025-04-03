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
   private ?RouteCollection $routes = null;

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
   private string|null $currentGroupPrefix = null;

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
    * Initialise le routeur
    */
   public static function init(): void
   {
      if (self::$routes === null) {
         self::$routes = new RouteCollection();
      }
   }

   /**
    * Ajoute une route
    * 
    * @param string $method La méthode HTTP
    * @param string $path L'URI de la route
    * @param mixed $handler L'action à exécuter
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
         $route->setPath($this->currentGroupPrefix . '/' . ltrim($path, '/'));
      }

      $this->routes->add($method . '_' . $path, $route);
      $this->lastRoute = $route;

      return $route;
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
    * Ajoute un groupe de routes
    * 
    * @param string $prefix Le préfixe des routes
    * @param callable $callback La fonction de callback
    * @param array $attributes Les attributs du groupe
    * @return self
    */
   public function group(string $prefix, callable $callback, array $attributes = []): self
   {
      $previousPrefix = $this->currentGroupPrefix;

      if ($previousPrefix !== null) {
         $this->currentGroupPrefix = $previousPrefix . '/' . ltrim($prefix, '/');
      } else {
         $this->currentGroupPrefix = rtrim($prefix, '/');
      }

      $previousMiddleware = $this->middleware;
      $this->middleware = array_merge($this->middleware, $attributes['middleware'] ?? []);

      $callback();

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
    */
   public function match(array $methods, string $uri, array|callable $action): void
   {
      foreach ($methods as $method) {
         $this->addRoute($method, $uri, $action);
      }
   }

   /**
    * Ajoute les routes d'authentification par défaut
    */
   public function auth(): void
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
   }

   /**
    * Ajoute les routes RESTful pour une ressource
    * 
    * @param string $uri L'URI de base de la ressource
    * @param string $controller Le contrôleur à utiliser
    * @return self
    */
   public function resource(string $uri, string $controller): self
   {
      $this->get($uri, [$controller, 'index'])->name($uri . '.index');
      $this->get($uri . '/create', [$controller, 'create'])->name($uri . '.create');
      $this->post($uri, [$controller, 'store'])->name($uri . '.store');
      $this->get($uri . '/{id}', [$controller, 'show'])->name($uri . '.show');
      $this->get($uri . '/{id}/edit', [$controller, 'edit'])->name($uri . '.edit');
      $this->put($uri . '/{id}', [$controller, 'update'])->name($uri . '.update');
      $this->delete($uri . '/{id}', [$controller, 'destroy'])->name($uri . '.destroy');

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
         $this->lastRoute->setPath($prefix . '/' . ltrim($currentPath, '/'));
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
    * @param string|array $middleware Les middlewares à ajouter
    * @return self
    */
   public function middleware(string|array $middleware): self
   {
      if ($this->lastRoute) {
         $currentMiddleware = $this->lastRoute->getDefault('_middleware') ?? [];
         $this->lastRoute->setDefault('_middleware', array_merge($currentMiddleware, (array) $middleware));
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
      $route = $this->getRoute($name);
      return $route->getPath();
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

      foreach ($this->routes as $name => $route) {
         if ($route->getMethods() === [$method] && $route->getPath() === $path) {
            $controller = $route->getDefault('_controller');
            if (is_array($controller)) {
               [$class, $method] = $controller;
               $instance = $this->container->make($class);
               return $instance->$method($request);
            }
            return $controller($request);
         }
      }

      throw new HttpException(404, "Route non trouvée : {$path}");
   }

   /**
    * Génère une URL pour une route nommée
    * 
    * @param string $name Le nom de la route
    * @param array<string, mixed> $parameters Les paramètres de la route
    * @return string L'URL générée
    */
   public function generateUrl(string $name, array $parameters = []): string
   {
      foreach ($this->routes as $route) {
         if ($route->getDefault('_name') === $name) {
            return $route->getPath();
         }
      }

      throw new \RuntimeException("Route non trouvée : {$name}");
   }
}
