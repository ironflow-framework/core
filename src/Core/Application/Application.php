<?php

declare(strict_types=1);

namespace IronFlow\Core\Application;

use IronFlow\Cache\Hammer\Hammer;
use IronFlow\Cache\Hammer\HammerManager;
use IronFlow\Core\Container\ContainerInterface;
use IronFlow\Core\Container\Container;
use IronFlow\Core\Exceptions\ErrorHandler;
use IronFlow\Core\Service\ServiceProvider;
use IronFlow\Database\Connection;
use IronFlow\Database\Iron\IronManager;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Providers\AppServiceProvider;
use IronFlow\Providers\CacheServiceProvider;
use IronFlow\Providers\DatabaseServiceProvider;
use IronFlow\Providers\RouteServiceProvider;
use IronFlow\Providers\TranslationServiceProvider;
use IronFlow\Providers\ViewServiceProvider;
use IronFlow\Routing\RouterInterface;
use IronFlow\Routing\Router;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Trans;
use IronFlow\View\TwigView;

/**
 * Classe principale de l'application IronFlow
 * 
 * Cette classe est responsable de l'initialisation et de la gestion du cycle de vie
 * de l'application. Elle implémente le pattern Singleton pour garantir une instance
 * unique de l'application.
 * 
 * @package IronFlow\Core\Application
 * @author IronFlow Team
 * @version 1.0.0
 */
class Application implements ApplicationInterface
{
   /**
    * Version actuelle du framework
    * 
    * @var string
    */
   public const VERSION = '1.0.0';

   /**
    * Instance unique de l'application (pattern Singleton)
    * 
    * @var self|null
    */
   private static ?self $instance = null;

   /**
    * Container d'injection de dépendances
    * 
    * @var ContainerInterface
    */
   private ContainerInterface $container;

   /**
    * Chemin de base de l'application
    * 
    * @var string
    */
   private string $basePath;

   /**
    * Liste des fournisseurs de services enregistrés
    * 
    * @var array<string>
    */
   private array $serviceProviders = [];

   /**
    * Liste des fournisseurs de services démarrés
    * 
    * @var array<string>
    */
   private array $bootedServiceProviders = [];

   /**
    * Chemin vers le fichier de routes web
    * 
    * @var string
    */
   private string $webRouterPath = '';

   /**
    * Chemin vers le fichier de routes API
    * 
    * @var string
    */
   private string $apiRouterPath = '';

   /**
    * Chemin vers le fichier de routes CraftPanel (Panneau d'administration)
    * 
    * @var string
    */
   private string $craftRouterPath = '';

   /**
    * Constructeur privé pour le pattern Singleton
    * 
    * @param string $basePath Chemin de base de l'application
    */
   private function __construct(string $basePath)
   {
      $this->basePath = $basePath;
      $this->container = new Container();
      $this->registerBaseBindings();
   }

   /**
    * Obtient l'instance unique de l'application
    * 
    * @param string|null $basePath Chemin de base de l'application (requis lors de la première initialisation)
    * @return self
    * @throws \RuntimeException Si le chemin de base n'est pas fourni lors de la première initialisation
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
    * 
    * Cette méthode initialise les services fondamentaux de l'application
    * comme le conteneur, la configuration, le routeur, etc.
    * 
    * @return void
    */
   private function registerBaseBindings(): void
   {
      // Liaisons de base
      $this->container->singleton('app', fn() => $this);
      $this->container->singleton('config', fn() => new Config());
      $this->container->singleton(ContainerInterface::class, fn() => $this->container);

      // Services HTTP
      $this->container->singleton(RouterInterface::class, fn() => new Router($this->container));
      $this->container->singleton(Request::class, fn() => Request::createFromGlobals());

      // Services de vue et de base de données
      $this->container->singleton('view', fn() => new TwigView(view_path() ?? '/resources/views'));
      $this->container->singleton('db', fn() => Connection::getInstance());
      $this->container->singleton('db.manager', fn() => new IronManager());

      // Services de cache et de traduction
      $this->container->singleton('cache', fn() => Hammer::getInstance());
      $this->container->singleton('cache.manager', fn() => new HammerManager(config('cache')));
      $this->container->singleton('translator', fn() => new Trans());

      // Enregistrement des fournisseurs de services par défaut
      $this->serviceProviders = [
         AppServiceProvider::class,
         ViewServiceProvider::class,
         RouteServiceProvider::class,
         DatabaseServiceProvider::class,
         CacheServiceProvider::class,
         TranslationServiceProvider::class
      ];
   }

   /**
    * Initialise l'application
    * 
    * Cette méthode charge la configuration, enregistre la gestion des erreurs,
    * initialise les fournisseurs de services et charge les routes.
    * 
    * @return void
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
    * Charge la configuration de l'application
    * 
    * Cette méthode charge les fichiers de configuration depuis le dossier config
    * et définit les valeurs par défaut si nécessaire.
    * 
    * @return void
    */
   private function loadConfiguration(): void
   {
      // Chargement de la configuration depuis les fichiers
      $config = $this->container->make('config');
      $config->loadFromPath($this->basePath . '/config');

      // Configuration par défaut si non définie
      if (!$config->has('app.locale')) {
         $config->set('app.locale', 'fr');
      }
      if (!$config->has('app.version')) {
         $config->set('app.version', self::VERSION);
      }
   }

   /**
    * Enregistre le gestionnaire d'erreurs
    * 
    * Cette méthode configure la gestion des erreurs et des exceptions
    * pour l'application.
    * 
    * @return void
    */
   private function registerErrorHandling(): void
   {
      $handler = new ErrorHandler($this);
      $handler->register();
   }

   /**
    * Enregistre tous les fournisseurs de services
    * 
    * Cette méthode initialise tous les fournisseurs de services
    * enregistrés dans l'application.
    * 
    * @return void
    */
   private function registerServiceProviders(): void
   {
      foreach ($this->serviceProviders as $provider) {
         $this->registerServiceProvider($provider);
      }
   }

   /**
    * Démarre tous les fournisseurs de services
    * 
    * Cette méthode appelle la méthode boot() sur chaque fournisseur
    * de service qui n'a pas encore été démarré.
    * 
    * @return void
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
    * Enregistre un fournisseur de service spécifique
    * 
    * @param string $provider Nom de la classe du fournisseur de service
    * @return void
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
    * 
    * Cette méthode est le point d'entrée principal de l'application.
    * Elle traite la requête HTTP et renvoie la réponse appropriée.
    * 
    * @return void
    */
   public function run(): void
   {
      $request = $this->container->make(Request::class);
      $response = $this->container->make(RouterInterface::class)->dispatch($request);
      $response->send();
   }

   /**
    * Gère une exception non capturée
    * 
    * Cette méthode convertit les exceptions en réponses HTTP appropriées
    * et affiche une page d'erreur selon le mode de débogage.
    * 
    * @param \Throwable $e L'exception à gérer
    * @return Response La réponse HTTP générée
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
    * 
    * @return ContainerInterface
    */
   public function getContainer(): ContainerInterface
   {
      return $this->container;
   }

   /**
    * Obtient le routeur de l'application
    * 
    * @return RouterInterface
    */
   public function getRouter(): RouterInterface
   {
      return $this->container->make(RouterInterface::class);
   }

   /**
    * Obtient le chemin de base de l'application
    * 
    * @return string
    */
   public function getBasePath(): string
   {
      return $this->basePath;
   }

   /**
    * Configure les chemins des fichiers de routes
    * 
    * @param string $web Chemin vers le fichier de routes web
    * @param string $api Chemin vers le fichier de routes API
    * @return static
    */
   public function withRouter(string $web, string $api): static
   {
      $this->webRouterPath = $web;
      $this->apiRouterPath = $api;

      if (file_exists($this->basePath . '/routes/craft.php')) {
         $this->craftRouterPath = $this->basePath . '/routes/craft.php';
      }

      return $this;
   }

   /**
    * Ajoute des fournisseurs de services supplémentaires
    * 
    * @param array<string> $providers Liste des fournisseurs de services à ajouter
    * @return static
    */
   public function withProvider(array $providers): static
   {
      $this->serviceProviders = array_merge($this->serviceProviders, $providers);
      return $this;
   }

   /**
    * Charge les fichiers de routes
    * 
    * Cette méthode charge les fichiers de routes web et API si ils existent.
    * 
    * @return void
    */
   private function loadRoutes(): void
   {
      // Les routes sont déjà chargées par le RouteServiceProvider
      // Cette méthode est conservée pour compatibilité mais ne fait rien
   }
}
