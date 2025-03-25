<?php

declare(strict_types=1);

namespace IronFlow\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Http\Exceptions\NotFoundException;
use App\Controllers\AuthController;

class Router
{
   private static ?RouteCollection $routes = null;
   private static array $middlewareGroups = [];
   private static array $middleware = [];
   private static array $globalMiddleware = [];
   private static array $namedRoutes = [];
   private static ?Route $lastRoute = null;

   public static function init(): void
   {
      if (self::$routes === null) {
         self::$routes = new RouteCollection();
      }
   }

   public static function get(string $path, $handler): self
   {
      self::addRoute('GET', $path, $handler);
      return new self();
   }

   public static function post(string $path, $handler): self
   {
      self::addRoute('POST', $path, $handler);
      return new self();
   }

   public static function put(string $path, $handler): self
   {
      self::addRoute('PUT', $path, $handler);
      return new self();
   }

   public static function delete(string $path, $handler): self
   {
      self::addRoute('DELETE', $path, $handler);
      return new self();
   }

   public function middleware(string|array $middleware): self
   {
      if (self::$lastRoute) {
         $currentMiddleware = self::$lastRoute->getDefault('_middleware') ?? [];
         self::$lastRoute->setDefault('_middleware', array_merge($currentMiddleware, (array) $middleware));
      }
      return $this;
   }

   public function name(string $name): self
   {
      if (self::$lastRoute) {
         self::$namedRoutes[$name] = self::$lastRoute;
         self::$lastRoute->setDefault('_name', $name);
      }
      return $this;
   }

   public static function group(array $attributes, callable $callback): void
   {
      $previousMiddleware = self::$middleware;
      self::$middleware = array_merge(self::$middleware, $attributes['middleware'] ?? []);

      $callback();

      self::$middleware = $previousMiddleware;
   }

   public static function auth(): void
   {
      // Routes d'authentification par défaut
      self::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
      self::post('/login', [AuthController::class, 'login']);
      self::post('/logout', [AuthController::class, 'logout'])->name('logout');
      self::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
      self::post('/register', [AuthController::class, 'register']);
      self::get('/password/reset', [AuthController::class, 'showResetForm'])->name('password.request');
      self::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
      self::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
      self::post('/password/reset', [AuthController::class, 'reset'])->name('password.update');
   }

   public static function resource(string $path, string $controller): self
   {
      $instance = new self();

      // Index
      self::get($path, [$controller, 'index'])->name($path . '.index');

      // Create
      self::get($path . '/create', [$controller, 'create'])->name($path . '.create');

      // Store
      self::post($path, [$controller, 'store'])->name($path . '.store');

      // Show
      self::get($path . '/{id}', [$controller, 'show'])->name($path . '.show');

      // Edit
      self::get($path . '/{id}/edit', [$controller, 'edit'])->name($path . '.edit');

      // Update
      self::put($path . '/{id}', [$controller, 'update'])->name($path . '.update');

      // Delete
      self::delete($path . '/{id}', [$controller, 'destroy'])->name($path . '.destroy');

      return $instance;
   }

   private static function addRoute(string $method, string $path, $handler): void
   {
      self::init();

      if (is_array($handler)) {
         $handler = self::resolveController($handler);
      }

      $path = preg_replace('#/+#', '/', $path);

      $route = new Route(
         $path,
         [
            '_controller' => $handler,
            '_middleware' => self::$middleware
         ],
         [],
         [],
         '',
         [],
         [$method]
      );

      $routeName = $method . '_' . str_replace('/', '_', trim($path, '/'));
      self::$routes->add($routeName, $route);
      self::$lastRoute = $route;
   }

   private static function resolveController(array $handler): callable
   {
      if (is_callable($handler)) {
         return $handler;
      }

      if (!is_array($handler)) {
         throw new \InvalidArgumentException("Le handler doit être un tableau [classe, méthode] ou une fonction callable");
      }

      [$class, $method] = $handler;

      return function (Request $request) use ($class, $method) {
         $controller = new $class();
         $reflection = new \ReflectionMethod($class, $method);
         $parameters = $reflection->getParameters();

         $args = [];
         foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type && $type->getName() === Request::class) {
               $args[] = $request;
            } else {
               $routeParams = $request->getRouteParameters();
               $args[] = $routeParams[$parameter->getName()] ?? null;
            }
         }

         return $controller->$method(...$args);
      };
   }

   public static function getRoutes(): RouteCollection
   {
      return self::$routes ?? new RouteCollection();
   }

   public static function match(string $path, string $method): array
   {
      $context = new RequestContext();
      $context->setMethod($method);
      $context->setPathInfo($path);

      $matcher = new UrlMatcher(self::getRoutes(), $context);

      try {
         return $matcher->match($path);
      } catch (ResourceNotFoundException $e) {
         throw new NotFoundException("Route {$path} not found.", 404, $e);
      }
   }

   public static function dispatch(Request $request): Response
   {
      try {
         $route = self::match($request->getPathInfo(), $request->getMethod());
         $handler = $route['_controller'];
         $middleware = array_merge(self::$globalMiddleware, $route['_middleware'] ?? []);

         unset($route['_controller'], $route['_middleware']);
         $request->setRouteParameters($route);

         $next = function (Request $request) use ($handler) {
            return $handler($request);
         };

         foreach (array_reverse($middleware) as $middleware) {
            $next = function (Request $request) use ($middleware, $next) {
               return (new $middleware())->handle($request, $next);
            };
         }

         return $next($request);
      } catch (NotFoundException $e) {
         throw $e;
      } catch (\Exception $e) {
         throw $e;
      }
   }

   public static function url(string $name, array $parameters = []): string
   {
      if (!isset(self::$namedRoutes[$name])) {
         throw new \RuntimeException("Route [{$name}] not defined.");
      }

      $route = self::$namedRoutes[$name];
      $path = $route->getPath();

      foreach ($parameters as $key => $value) {
         $path = str_replace("{{$key}}", (string) $value, $path);
      }

      return $path;
   }
}
