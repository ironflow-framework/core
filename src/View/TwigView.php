<?php

declare(strict_types=1);

namespace IronFlow\View;

use IronFlow\Support\Facades\Filesystem;
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
      error_log("=== Début de l'initialisation de TwigView ===");
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

      $cachePath = storage_path('cache/twig');
      error_log("Chemin du cache: " . $cachePath);

      if (!is_dir($cachePath)) {
         error_log("Création du répertoire de cache...");
         if (!Filesystem::makeDirectory($cachePath, 0777, true)) {
            throw new \RuntimeException("Impossible de créer le répertoire de cache: " . $cachePath);
         }
         error_log("Répertoire de cache créé avec succès");
      }

      if (!is_writable($cachePath)) {
         error_log("ERREUR: Le répertoire de cache n'est pas accessible en écriture: " . $cachePath);
         throw new \RuntimeException("Le répertoire de cache n'est pas accessible en écriture: " . $cachePath);
      }

      $this->twig = new Environment($loader, [
         'cache' => $cachePath,
         'auto_reload' => true,
         'debug' => true,
         'strict_variables' => true,
         'charset' => 'UTF-8'
      ]);

      $this->twig->addExtension(new ViteExtension());
      $this->twig->addExtension(new RouteExtension());
     
      error_log("TwigView initialisé avec succès");
      error_log("Cache path: " . $cachePath);
      error_log("=== Fin de l'initialisation de TwigView ===");
   }

   public function render(string $template, array $data = []): string
   {
      error_log("=== Début du rendu du template ===");
      error_log("Template demandé: " . $template);
      error_log("Données passées: " . print_r($data, true));

      try {
         // Si le template commence par un point, on le retire
         $template = ltrim($template, '.');

         // Si le template ne contient pas de point, on ajoute .twig directement
         if (!str_contains($template, '.')) {
            $templatePath = $template . '.twig';
         } else {
            // Sinon on remplace les points par des slashes
            $templatePath = str_replace('.', '/', $template) . '.twig';
         }

         error_log("Chemin complet du template: " . $templatePath);
         error_log("Loader disponible: " . ($this->twig->getLoader() ? 'Oui' : 'Non'));

         if (!$this->twig->getLoader()->exists($templatePath)) {
            error_log("ERREUR: Le template n'existe pas: " . $templatePath);
            error_log("Liste des templates disponibles:");
            $loader = $this->twig->getLoader();
            if ($loader instanceof FilesystemLoader) {
               $paths = $loader->getPaths();
               foreach ($paths as $path) {
                  error_log("- " . $path);
               }
            }
            throw new \RuntimeException("Le template n'existe pas: " . $templatePath);
         }

         // Fusionner les données globales avec les données du template
         $mergedData = array_merge($this->globalData, $data);
         error_log("Données fusionnées: " . print_r($mergedData, true));

         $content = $this->twig->render($templatePath, $mergedData);
         error_log("Rendu réussi");
         error_log("=== Fin du rendu du template ===");
         return $content;
      } catch (\Exception $e) {
         error_log("ERREUR lors du rendu: " . $e->getMessage());
         error_log("Trace: " . $e->getTraceAsString());
         error_log("=== Fin du rendu du template (avec erreur) ===");
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

}
