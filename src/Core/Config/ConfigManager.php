<?php

declare(strict_types=1);

namespace IronFlow\Core\Config;

use IronFlow\Core\Config\Loaders\PhpLoader;
use IronFlow\Core\Config\Loaders\JsonLoader;
use IronFlow\Core\Config\Loaders\YamlLoader;
use IronFlow\Core\Config\Exceptions\ConfigException;

class ConfigManager
{
   private static ?ConfigManager $instance = null;
   private array $items = [];
   private array $loaders = [];
   private string $environment;

   private function __construct()
   {
      $this->environment = env('APP_ENV', 'production');
      $this->registerDefaultLoaders();
      $this->loadConfigurations();
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   private function registerDefaultLoaders(): void
   {
      $this->loaders = [
         'php' => new PhpLoader(),
         'json' => new JsonLoader(),
         'yaml' => new YamlLoader(),
      ];
   }

   private function loadConfigurations(): void
   {
      $configPath = base_path('config');
      $files = glob($configPath . '/*.*');

      foreach ($files as $file) {
         $this->loadFile($file);
      }

      // Charger les configurations spécifiques à l'environnement
      $envPath = $configPath . '/' . $this->environment;
      if (is_dir($envPath)) {
         $envFiles = glob($envPath . '/*.*');
         foreach ($envFiles as $file) {
            $this->loadFile($file, true);
         }
      }
   }

   private function loadFile(string $file, bool $override = false): void
   {
      $extension = pathinfo($file, PATHINFO_EXTENSION);
      $name = pathinfo($file, PATHINFO_FILENAME);

      if (!isset($this->loaders[$extension])) {
         throw new ConfigException("Format de configuration non supporté: {$extension}");
      }

      $config = $this->loaders[$extension]->load($file);

      if ($override) {
         $this->items[$name] = array_replace_recursive($this->items[$name] ?? [], $config);
      } else {
         $this->items[$name] = $config;
      }
   }

   public function get(string $key, mixed $default = null): mixed
   {
      $segments = explode('.', $key);
      $config = $this->items;

      foreach ($segments as $segment) {
         if (!is_array($config) || !array_key_exists($segment, $config)) {
            return $default;
         }
         $config = $config[$segment];
      }

      return $config;
   }

   public function set(string $key, mixed $value): void
   {
      $segments = explode('.', $key);
      $config = &$this->items;

      foreach ($segments as $i => $segment) {
         if ($i === count($segments) - 1) {
            $config[$segment] = $value;
            break;
         }

         if (!isset($config[$segment]) || !is_array($config[$segment])) {
            $config[$segment] = [];
         }

         $config = &$config[$segment];
      }
   }

   public function has(string $key): bool
   {
      return $this->get($key) !== null;
   }

   public function all(): array
   {
      return $this->items;
   }

   public function getEnvironment(): string
   {
      return $this->environment;
   }

   public function registerLoader(string $extension, LoaderInterface $loader): void
   {
      $this->loaders[$extension] = $loader;
   }

   public function reload(): void
   {
      $this->items = [];
      $this->loadConfigurations();
   }
}
