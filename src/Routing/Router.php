<?php

declare(strict_types=1);

namespace IronFlow\Routing;

use Closure;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Routing\Exceptions\RouteNotFoundException;
use IronFlow\Core\Container\ContainerInterface;
use App\Controllers\AuthController;
use IronFlow\Core\Exceptions\HttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
    * Les patterns de paramètres personnalisés
    * 
    * @var array<string, string>
    */
   private array $patterns = [];

   /**
    * La pile des groupes de routes
    * 
    * @var array<array>
    */
   private array $groupStack = [];

   /**
    * Crée une nouvelle instance du routeur
    * 
    * @param ContainerInterface $container Le conteneur d'injection de dépendances
    */
   public function __construct(ContainerInterface $container)
   {
      $this->container = $container;
      $this->routes = new RouteCollection();
      $this->registerDefaultPatterns();
   }

   /**
    * Enregistre les patterns par défaut pour les paramètres de route
    */
   private function registerDefaultPatterns(): void
   {
      $this->patterns = [
         'id' => '[0-9]+',
         'slug' => '[a-z0-9-]+',
         'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
         'any' => '.*',
      ];
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
      // Normaliser le chemin
      $normalizedPath = $this->normalizePath($path);

      // Créer la route avec les patterns personnalisés
      $route = new Route(
         $normalizedPath,
         ['_controller' => $handler],
         $this->compileRoutePatterns($normalizedPath),
         [],
         '',
         [],
         [$method]
      );

      // Appliquer le préfixe du groupe si nécessaire
      if ($this->currentGroupPrefix !== null) {
         $route->setPath($this->normalizePath($this->currentGroupPrefix . '/' . ltrim($normalizedPath, '/')));
      }

      // Générer un nom unique pour la route
      $routeName = $method . '_' . $route->getPath();

      // Ajouter les middlewares globaux à la route
      $route->setDefault('_middleware', $this->middleware);

      // Ajouter la route à la collection
      $this->routes->add($routeName, $route);
      $this->lastRoute = $route;

      return $route;
   }

   /**
    * Compile les patterns personnalisés pour une route
    * 
    * @param string $path Le chemin de la route
    * @return array<string, string> Les patterns compilés
    */
   private function compileRoutePatterns(string $path): array
   {
      $requirements = [];

      // Extraire les paramètres de la route
      preg_match_all('/\{([a-zA-Z0-9_]+)(?::([^}]+))?\}/', $path, $matches, PREG_SET_ORDER);

      foreach ($matches as $match) {
         $paramName = $match[1];

         // Si un pattern spécifique est défini dans la route avec {param:pattern}
         if (isset($match[2])) {
            $requirements[$paramName] = $match[2];
         }
         // Sinon, vérifier si un pattern est défini globalement
         elseif (isset($this->patterns[$paramName])) {
            $requirements[$paramName] = $this->patterns[$paramName];
         }
      }

      return $requirements;
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
      // Sauvegarde de l'état actuel pour la pile de groupes
      $this->groupStack[] = [
         'prefix' => $this->currentGroupPrefix,
         'middleware' => $this->middleware
      ];

      // Définition du nouveau préfixe
      if ($this->currentGroupPrefix !== null) {
         $this->currentGroupPrefix = $this->normalizePath($this->currentGroupPrefix . '/' . ltrim($prefix, '/'));
      } else {
         $this->currentGroupPrefix = rtrim($prefix, '/');
      }

      // Gestion des middlewares du groupe
      if (isset($attributes['middleware'])) {
         $middlewareToAdd = is_array($attributes['middleware'])
            ? $attributes['middleware']
            : [$attributes['middleware']];

         $this->middleware = array_merge($this->middleware, $middlewareToAdd);
      }

      // Exécution du callback avec le nouveau contexte
      $callback($this);

      // Restauration de l'état précédent
      $lastGroup = array_pop($this->groupStack);
      $this->currentGroupPrefix = $lastGroup['prefix'];
      $this->middleware = $lastGroup['middleware'];

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
    * @param string $controller Le contrôleur à utiliser
    * @return self
    */
   public function auth(string $controller = AuthController::class): self
   {
      $this->get('/login', [$controller, 'showLoginForm'])->name('login');
      $this->post('/login', [$controller, 'login']);
      $this->post('/logout', [$controller, 'logout'])->name('logout');
      $this->get('/register', [$controller, 'showRegistrationForm'])->name('register');
      $this->post('/register', [$controller, 'register']);
      $this->get('/password/reset', [$controller, 'showResetForm'])->name('password.request');
      $this->post('/password/email', [$controller, 'sendResetLinkEmail'])->name('password.email');
      $this->get('/password/reset/{token}', [$controller, 'showResetForm'])->name('password.reset');
      $this->post('/password/reset', [$controller, 'reset'])->name('password.update');

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
   public function getRouteByName(string $name): Route
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
    * @return Response La réponse générée
    * @throws HttpException Si la route n'est pas trouvée
    */
   public function dispatch(Request $request): Response
   {
      try {
         // Créer le contexte de la requête
         $context = new RequestContext();
         $context->fromRequest($request);

         // Créer le matcher d'URL
         $matcher = new UrlMatcher($this->routes, $context);

         // Trouver la route correspondante
         $parameters = $matcher->match($request->getPathInfo());

         // Extraire le contrôleur et les paramètres
         $route = $this->routes->get($parameters['_route']);

         // Exécuter les middlewares
         $response = $this->runMiddleware($route, $request);
         if ($response instanceof Response) {
            return $response;
         }

         // Exécuter l'action de la route
         return $this->runRoute($route, $request, $parameters);
      } catch (ResourceNotFoundException $e) {
         throw new RouteNotFoundException("Route non trouvée: {$request->getMethod()} {$request->getPathInfo()}", [], $e);
      } catch (RouteNotFoundException $e) {
         return new Response('Page non trouvée', 404);
      }
   }

   /**
    * Exécute les middlewares d'une route
    *
    * @param Route $route La route
    * @param Request $request La requête
    * @return Response|null Une réponse si un middleware interrompt le flux, null sinon
    */
   private function runMiddleware(Route $route, Request $request): ?Response
   {
      $middlewares = $route->getDefault('_middleware') ?? [];

      foreach ($middlewares as $middleware) {
         $instance = $this->container->make($middleware);
         $response = $instance->handle($request, function ($request) {
            return null;
         });

         // Si le middleware retourne une réponse, on arrête le traitement
         if ($response instanceof Response) {
            return $response;
         }
      }

      return null;
   }

   /**
    * Exécute l'action d'une route
    *
    * @param Route $route La route
    * @param Request $request La requête
    * @param array $parameters Les paramètres de la route
    * @return Response La réponse
    * @throws RouteNotFoundException Si l'action est invalide
    */
   private function runRoute(Route $route, Request $request, array $parameters): Response
   {
      $action = $route->getDefault('_controller');

      if ($action instanceof Closure) {
         // Injecter les paramètres de la route dans la fonction anonyme
         return $action($request, ...array_filter($parameters, function ($key) {
            return !str_starts_with($key, '_');
         }, ARRAY_FILTER_USE_KEY));
      }

      if (is_array($action)) {
         $class = $action[0];
         $method = $action[1] ?? '__invoke';

         return $this->runControllerAction($class, $method, $request, $parameters);
      }

      if (is_string($action)) {
         // Gérer les actions au format "Controller@method"
         if (strpos($action, '@') !== false) {
            list($class, $method) = explode('@', $action, 2);
            return $this->runControllerAction($class, $method, $request, $parameters);
         }

         // Gérer les actions au format "Controller"
         return $this->runControllerAction($action, '__invoke', $request, $parameters);
      }

      throw new RouteNotFoundException('Action de route invalide');
   }

   /**
    * Exécute une action de contrôleur
    *
    * @param string $class La classe du contrôleur
    * @param string $method La méthode à appeler
    * @param Request $request La requête
    * @param array $parameters Les paramètres de la route
    * @return Response La réponse
    * @throws HttpException Si la méthode n'existe pas
    */
   private function runControllerAction(string $class, string $method, Request $request, array $parameters): Response
   {
      $controller = $this->container->make($class);

      if (!method_exists($controller, $method)) {
         throw new HttpException(500, "La méthode [{$method}] n'existe pas dans le contrôleur [{$class}]");
      }

      // Filtrer les paramètres internes qui commencent par '_'
      $routeParams = array_filter($parameters, function ($key) {
         return !str_starts_with($key, '_');
      }, ARRAY_FILTER_USE_KEY);

      // Appeler la méthode du contrôleur avec la requête et les paramètres de route
      return $controller->{$method}($request, ...$routeParams);
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
      $route = $this->getRouteByName($name);
      $path = $route->getPath();

      // Vérifier les paramètres requis
      preg_match_all('/\{([a-zA-Z0-9_]+)(?::[^}]+)?\}/', $path, $requiredParams);

      foreach ($requiredParams[1] as $param) {
         if (!isset($parameters[$param])) {
            throw new HttpException(500, "Missing parameter [{$param}] for route [{$name}]");
         }

         // Valider le paramètre par rapport au pattern requis
         $requirements = $route->getRequirements();
         if (isset($requirements[$param]) && !preg_match('/^' . $requirements[$param] . '$/', (string)$parameters[$param])) {
            throw new HttpException(500, "Parameter [{$param}] with value [{$parameters[$param]}] does not match the required pattern for route [{$name}]");
         }
      }

      // Remplacer les paramètres dans l'URL (supprimer aussi les portions de pattern {:pattern})
      foreach ($parameters as $key => $value) {
         $path = preg_replace('/\{' . preg_quote($key, '/') . '(?::[^}]+)?\}/', (string) $value, $path);
      }

      return $path;
   }

   /**
    * Définit un pattern personnalisé pour un paramètre
    *
    * @param string $key Le nom du paramètre
    * @param string $pattern Le pattern regex
    * @return self
    */
   public function pattern(string $key, string $pattern): self
   {
      $this->patterns[$key] = $pattern;
      return $this;
   }

   /**
    * Récupère tous les patterns définis
    * 
    * @return array<string, string>
    */
   public function getPatterns(): array
   {
      return $this->patterns;
   }

   /**
    * Récupère la pile des groupes
    * 
    * @return array
    */
   public function getGroupStack(): array
   {
      return $this->groupStack;
   }

   /**
    * Récupère le préfixe de groupe actuel
    * 
    * @return string|null
    */
   public function getCurrentGroupPrefix(): ?string
   {
      return $this->currentGroupPrefix;
   }

   /**
    * Récupère la dernière route ajoutée
    * 
    * @return Route|null
    */
   public function getLastRoute(): ?Route
   {
      return $this->lastRoute;
   }

   /**
    * Récupère les routes nommées
    * 
    * @return array<string, Route>
    */
   public function getNamedRoutes(): array
   {
      return $this->namedRoutes;
   }
   
   /**
    * Récupère les middlewares globaux
    * 
    * @return array<string>
    */
   public function getMiddleware(): array
   {
      return $this->middleware;
   }
}
