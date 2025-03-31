<?php

declare(strict_types=1);

namespace IronFlow\Core;

use Closure;
use IronFlow\Core\Container;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Routing\Router;
use IronFlow\Core\Exceptions\ErrorHandler;
use IronFlow\Support\Facades\Config;
use IronFlow\Core\Providers\ServiceProvider;
use IronFlow\Support\Facades\Filesystem;
use IronFlow\View\TwigView;

/**
 * Classe principale de l'application IronFlow
 */
class Application
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
   private Container $container;

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
   private array $bootedProviders = [];

   /**
    * Indique si l'application a été démarrée
    */
   private bool $booted = false;

   /**
    * Constructeur
    */
   public function __construct(?string $basePath = null)
   {
      $this->container = new Container();
      $this->container->instance('app', $this);
      $this->container->instance(self::class, $this);
      $this->container->instance(Container::class, $this->container);

      if ($basePath) {
         $this->setBasePath($basePath);
      } else {
         $this->basePath = dirname(__DIR__, 2);
      }

      self::$instance = $this;

      // Création des répertoires nécessaires
      $directories = [
         view_path(),
         storage_path('cache'),
         storage_path('logs'),
         storage_path('sessions'),
         public_path('assets'),
         resource_path('views/layouts'),
         resource_path('views/components'),
         resource_path('views/errors')
      ];

      foreach ($directories as $directory) {
         if (!Filesystem::exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
               throw new \RuntimeException("Impossible de créer le répertoire: {$directory}");
            }
         }
      }

      // Initialisation de la configuration
      Config::load();

      // Initialisation du moteur de vue
      $view = new TwigView(view_path());
      Response::setView($view);

      // Enregistrement du gestionnaire d'erreurs
      ErrorHandler::register();
   }

   /**
    * Récupère l'instance unique de l'application
    */
   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   /**
    * Définit le chemin de base de l'application
    */
   public function setBasePath(string $basePath): self
   {
      $this->basePath = $basePath;
      $this->bindPathsInContainer();
      return $this;
   }

   /**
    * Enregistre les chemins dans le container
    */
   protected function bindPathsInContainer(): void
   {
      $this->container->instance('path', $this->basePath);
      $this->container->instance('path.app', $this->basePath . '/app');
      $this->container->instance('path.config', $this->basePath . '/config');
      $this->container->instance('path.public', $this->basePath . '/public');
      $this->container->instance('path.storage', $this->basePath . '/storage');
      $this->container->instance('path.database', $this->basePath . '/database');
      $this->container->instance('path.resources', $this->basePath . '/resources');
      $this->container->instance('path.bootstrap', $this->basePath . '/bootstrap');
      $this->container->instance('path.routes', $this->basePath . '/routes');
   }

   /**
    * Récupère le container de l'application
    */
   public function getContainer(): Container
   {
      return $this->container;
   }

   /**
    * Enregistre un service singleton dans le container
    */
   public function singleton(string $abstract, Closure $concrete): void
   {
      $this->container->singleton($abstract, $concrete);
   }

   /**
    * Récupère un service du container
    */
   public function make(string $abstract): mixed
   {
      return $this->container->get($abstract);
   }

   /**
    * Enregistre un fournisseur de services
    */
   public function register(string $provider): self
   {
      if (!is_subclass_of($provider, ServiceProvider::class)) {
         throw new \InvalidArgumentException("La classe {$provider} n'est pas un fournisseur de services valide");
      }

      if (isset($this->serviceProviders[$provider])) {
         return $this;
      }

      $instance = new $provider($this);
      $instance->register();

      $this->serviceProviders[$provider] = $instance;

      if ($this->booted) {
         $this->bootProvider($instance);
      }

      return $this;
   }

   /**
    * Démarre un fournisseur de services
    */
   protected function bootProvider(ServiceProvider $provider): void
   {
      if (isset($this->bootedProviders[get_class($provider)])) {
         return;
      }

      $provider->boot();

      $this->bootedProviders[get_class($provider)] = true;
   }

   /**
    * Démarre l'application et les fournisseurs de services
    */
   public function boot(): void
   {
      if ($this->booted) {
         return;
      }

      foreach ($this->serviceProviders as $provider) {
         $this->bootProvider($provider);
      }

      $this->booted = true;
   }

   /**
    * Configure l'application avec des valeurs
    */
   public function configure(array $config): self
   {

      foreach ($config as $key => $value) {
         Config::set($key, $value);
      }

      // Chargement des fournisseurs de services
      $services = require config_path('services.php') ?? Config::get('providers');

      if (isset($services['providers'])) {
         foreach ($services['providers'] as $service) {
            $this->register($service);
         }
      }

      return $this;
   }

   /**
    * Charge les routes de l'application
    */
   public function withRoutes(array $files): self
   {
      Router::init();

      foreach ($files as $file) {
         if (file_exists($file)) {
            require $file;
         }
      }

      return $this;
   }

   /**
    * Exécute l'application
    */
   public function run(): Response
   {
      try {
         $this->boot();

         $request = Request::capture();
         $response = Router::dispatch($request);

         if (!($response instanceof Response)) {
            $response = new Response((string) $response);
         }

         $response->send();
         return $response;
      } catch (\Throwable $e) {
         return $this->handleException($e);
      }
   }

   /**
    * Gère une exception
    */
   protected function handleException(\Throwable $e): Response
   {
      if ($this->container->has(ErrorHandler::class)) {
         $handler = $this->container->get(ErrorHandler::class);
         return $handler->handle($e);
      }

      throw $e;
   }

   /**
    * Récupère le chemin de base de l'application
    */
   public function getBasePath(): string
   {
      return $this->basePath;
   }

   /**
    * Récupère un chemin relatif au chemin de base
    */
   public function path(string $path = ''): string
   {
      return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
   }
}
