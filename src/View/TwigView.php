<?php

declare(strict_types=1);

namespace IronFlow\View;

use IronFlow\Support\Facades\Config;
use IronFlow\View\Twig\ViteExtension;
use IronFlow\View\Twig\RouteExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigView implements ViewInterface
{
   private Environment $twig;
   protected array $globalData = [];

   public function __construct(string $viewsPath)
   {
      error_log("Type de viewsPath: " . gettype($viewsPath));
      error_log("Valeur de viewsPath: " . $viewsPath);
      error_log("Chemin absolu du fichier: " . __FILE__);
      error_log("Chemin absolu du dossier: " . dirname(__DIR__));
      error_log("Chemin absolu du projet: " . dirname(dirname(__DIR__)));

      if (!is_string($viewsPath)) {
         throw new \InvalidArgumentException("Le chemin des vues doit être une chaîne de caractères");
      }

      $viewsPath = realpath($viewsPath);
      error_log("Chemin absolu après realpath: " . $viewsPath);

      if ($viewsPath === false) {
         error_log("ERREUR: Impossible de résoudre le chemin: " . $viewsPath);
         throw new \RuntimeException("Impossible de résoudre le chemin des vues: " . $viewsPath);
      }

      if (!is_dir($viewsPath)) {
         error_log("ERREUR: Le répertoire des vues n'existe pas: " . $viewsPath);
         error_log("Création du répertoire des vues...");
         if (!mkdir($viewsPath, 0755, true)) {
            throw new \RuntimeException("Impossible de créer le répertoire des vues: " . $viewsPath);
         }
         error_log("Répertoire des vues créé avec succès");
      }

      error_log("Création du FilesystemLoader avec le chemin: " . $viewsPath);
      $loader = new FilesystemLoader($viewsPath);
      error_log("FilesystemLoader créé avec succès");
      error_log("Dossiers de recherche du loader: " . print_r($loader->getPaths(), true));

      $cachePath = dirname(__DIR__, 2) . '/storage/cache/twig';
      if (!is_dir($cachePath)) {
         error_log("Création du répertoire de cache...");
         if (!mkdir($cachePath, 0777, true)) {
            throw new \RuntimeException("Impossible de créer le répertoire de cache: " . $cachePath);
         }
         error_log("Répertoire de cache créé avec succès");
      }

      $this->twig = new Environment($loader, [
         'cache' => $cachePath,
         'auto_reload' => true,
         'debug' => true,
         'strict_variables' => true,
         'charset' => 'UTF-8'
      ]);

      $this->addGlobal('APP_LANG', Config::get('app.local'));
      $this->addGlobal('APP_VERSION', Config::get('app.version', '1.0.0'));

      $this->twig->addExtension(new ViteExtension());
      $this->twig->addExtension(new RouteExtension());

      // Ajout des fonctions Twig
      $this->twig->addFunction(new \Twig\TwigFunction('url', [$this, 'url']));
      $this->twig->addFunction(new \Twig\TwigFunction('asset', [$this, 'asset']));
      $this->twig->addFunction(new \Twig\TwigFunction('route', [$this, 'route']));

      error_log("TwigView initialisé avec succès");
      error_log("Cache path: " . $cachePath);
   }

   public function render(string $template, array $data = []): string
   {
      error_log("Rendu du template: " . $template);
      error_log("Données passées: " . print_r($data, true));

      try {
         $templatePath = str_replace('.', '/', $template) . '.twig';
         error_log("Chemin complet du template: " . $templatePath);
         error_log("Loader disponible: " . ($this->twig->getLoader() ? 'Oui' : 'Non'));
         error_log("Dossiers de recherche du loader: " . print_r($this->twig->getLoader()->getSourceContext($templatePath)->getPath(), true));

         if (!$this->twig->getLoader()->exists($templatePath)) {
            error_log("ERREUR: Le template n'existe pas: " . $templatePath);
            error_log("Liste des templates disponibles:");
            $paths = $this->twig->getLoader()->getSourceContext($templatePath)->getPath();
            error_log("- " . $paths);
            throw new \RuntimeException("Le template n'existe pas: " . $templatePath);
         }

         $content = $this->twig->render($templatePath, $data);
         error_log("Rendu réussi");
         return $content;
      } catch (\Exception $e) {
         error_log("Erreur lors du rendu: " . $e->getMessage());
         error_log("Trace: " . $e->getTraceAsString());
         throw $e;
      }
   }

   public function addGlobal(string $name, mixed $value): void
   {
      $this->twig->addGlobal($name, $value);
   }

   public function addFilter(string $name, callable $filter): void
   {
      $this->twig->addFilter(new \Twig\TwigFilter($name, $filter));
   }

   public function addFunction(string $name, callable $function): void
   {
      $this->twig->addFunction(new \Twig\TwigFunction($name, $function));
   }

   public function url(string $path, array $parameters = []): string
   {
      $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
      $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
      $path = trim($path, '/');
      $query = !empty($parameters) ? '?' . http_build_query($parameters) : '';
      return $baseUrl . '/' . $path . $query;
   }

   public function asset(string $path): string
   {
      return '/assets/' . ltrim($path, '/');
   }

   public function route(string $name, array $parameters = []): string
   {
      // Pour l'instant, on utilise une simple transformation
      // À améliorer avec un vrai système de nommage des routes
      $path = str_replace('.', '/', $name);
      return $this->url($path, $parameters);
   }
}
