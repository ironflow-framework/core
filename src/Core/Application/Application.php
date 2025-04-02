<?php

declare(strict_types=1);

namespace IronFlow\Core\Application;

use IronFlow\Core\Container\ContainerInterface;
use IronFlow\Core\Container\Container;
use IronFlow\Core\Exceptions\ErrorHandler;
use IronFlow\Core\Providers\ServiceProvider;
use IronFlow\Database\Connection;
use IronFlow\Http\Request;
use IronFlow\Routing\RouterInterface;
use IronFlow\Routing\Router;
use IronFlow\Support\Facades\Config;


class Application implements ApplicationInterface
{
   /**
    * Version du framework
    */
   public const VERSION = '1.0.0';

   /**
    * Instance unique de l'application
    */
   private static ?self $instance = null;

   /**
    * Container d'injection de dépendances
    */
   private ContainerInterface $container;

   /**
    * Chemin de base de l'application
    */
   private string $basePath;

   /**
    * Liste des fournisseurs de services enregistrés
    */
   private array $serviceProviders = [];

   /**
    * Liste des fournisseurs de services démarrés
    */
   private array $bootedServiceProviders = [];

   /**
    * Liste des fichiers routes
    */
   private array $routePaths = [];

   /**
    * Constructeur privé pour le pattern Singleton
    */
   private function __construct(string $basePath)
   {
      $this->basePath = $basePath;
      $this->container = new Container();
      $this->registerBaseBindings();
   }

   /**
    * Obtient l'instance unique de l'application
    */
   public static function getInstance(?string $basePath = null): self
   {
      if (self::$instance === null) {
         if ($basePath === null) {
            throw new \RuntimeException('Le chemin de base doit être fourni lors de la première initialisation');
         }
         self::$instance = new self($basePath);
      }
      return self::$instance;
   }

   /**
    * Enregistre les liaisons de base dans le conteneur
    */
   private function registerBaseBindings(): void
   {
      $this->container->singleton('app', fn() => $this);
      $this->container->singleton('config', fn() => new Config());
      $this->container->singleton(RouterInterface::class, fn() => new Router($this->container));
      $this->container->singleton(Request::class, fn() => Request::createFromGlobals());
      $this->container->singleton(Connection::class, fn() => new Connection());
   }

   /**
    * Initialise l'application
    */
   public function bootstrap(): void
   {
      $this->loadConfiguration();
      $this->registerErrorHandling();
      $this->registerServiceProviders();
      $this->bootServiceProviders();
      $this->loadRoutes();
   }

   /**
    * Charge la configuration
    */
   private function loadConfiguration(): void
   {
      // Chargement de la configuration depuis les fichiers
      $config = $this->container->make('config');
      $config->loadFromPath($this->basePath . '/config');
   }

   /**
    * Enregistre la gestion des erreurs
    */
   private function registerErrorHandling(): void
   {
      $handler = new ErrorHandler($this);
      $handler->register();
   }

   /**
    * Enregistre les fournisseurs de services
    */
   private function registerServiceProviders(): void
   {
      foreach ($this->serviceProviders as $provider) {
         $this->registerServiceProvider($provider);
      }
   }

   /**
    * Démarre les fournisseurs de services
    */
   private function bootServiceProviders(): void
   {
      foreach ($this->serviceProviders as $provider) {
         if (!in_array($provider, $this->bootedServiceProviders)) {
            $instance = $this->container->make($provider);
            if ($instance instanceof ServiceProvider) {
               $instance->boot();
               $this->bootedServiceProviders[] = $provider;
            }
         }
      }
   }

   /**
    * Enregistre un fournisseur de service
    */
   public function registerServiceProvider(string $provider): void
   {
      if (!in_array($provider, $this->serviceProviders)) {
         $instance = $this->container->make($provider);
         if ($instance instanceof ServiceProvider) {
            $instance->register();
            $this->serviceProviders[] = $provider;
         }
      }
   }

   /**
    * Exécute l'application
    */
   public function run(): void
   {
      $request = $this->container->make(Request::class);
      $response = $this->container->make(RouterInterface::class)->dispatch($request);
      $response->send();
   }

   /**
    * Gère une exception
    */
   public function handleException(\Throwable $e): Response
   {
      try {
         $statusCode = match (get_class($e)) {
            'IronFlow\Http\Exceptions\NotFoundException' => 404,
            'IronFlow\Http\Exceptions\ForbiddenException' => 403,
            'IronFlow\Http\Exceptions\UnauthorizedException' => 401,
            default => 500
         };

         if (Config::get('app.debug', false)) {
            return Response::view('errors/debug', [
               'exception' => $e,
               'message' => $e->getMessage(),
               'file' => $e->getFile(),
               'line' => $e->getLine(),
               'trace' => $e->getTraceAsString()
            ], $statusCode);
         }

         return Response::view("errors/{$statusCode}", [], $statusCode);
      } catch (\Throwable $e) {
         // Fallback en cas d'erreur lors du rendu de la vue
         http_response_code(500);
         echo "Une erreur est survenue.";
         exit;
      }
   }

   /**
    * Obtient le conteneur d'injection de dépendances
    */
   public function getContainer(): ContainerInterface
   {
      return $this->container;
   }

   /**
    * Obtient le routeur
    */
   public function getRouter(): RouterInterface
   {
      return $this->container->make(RouterInterface::class);
   }

   /**
    * Définit les chemins des fichiers de routes
    * 
    * @param string $web Chemin vers le fichier de routes web
    * @param string $api Chemin vers le fichier de routes API
    * @return static Instance courante de l'application
    */
   public function withRouter(string $web, string $api): static
   {
      $webRouterPath = ltrim($web, '/');
      $apiRouterPath = ltrim($api, '/');

      $webRoutesFile = $this->basePath . $webRouterPath;
      $apiRoutesFile = $this->basePath . $apiRouterPath;

      if (!file_exists($webRoutesFile)) {
         throw new ErrorHandler("Fichier de routes web non trouvé: {$webRoutesFile}");
      }

      if (!file_exists($apiRoutesFile)) {
         throw new ErrorHandler("Fichier de routes API non trouvé: {$apiRoutesFile}");
      }

      array_push($this->routePaths, $webRoutesFile);
      array_push($this->routePaths, $apiRoutesFile);

      return $this;
   }

   /**
    * Obtient le chemin de base de l'application
    */
   public function getBasePath(): string
   {
      return $this->basePath;
   }

   /**
    * Charge les routes de l'application
    */
   private function loadRoutes(): void
   {
      if (!file_exists($this->basePath . '/routes/web.php')) {
         require $this->basePath . '/routes/web.php';
      }

      foreach ($this->routePaths as $path) {
         require $path;
      }
   }
}
