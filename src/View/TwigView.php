<?php

declare(strict_types=1);

namespace IronFlow\View;

use IronFlow\Components\ComponentManager;
use IronFlow\Support\Facades\Filesystem;
use IronFlow\View\Twig\AuthExtension;
use IronFlow\View\Twig\CustomFilterExtension;
use IronFlow\View\Twig\ViteExtension;
use IronFlow\View\Twig\RouteExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Error\LoaderError;

class TwigView implements ViewInterface
{
   private Environment $twig;
   protected array $globalData = [];

   public function __construct(string $viewsPath)
   {
      if (!is_string($viewsPath)) {
         throw new \InvalidArgumentException("Le chemin des vues doit être une chaîne de caractères");
      }

      $viewsPath = realpath($viewsPath);
      if ($viewsPath === false) {
         throw new \RuntimeException("Impossible de résoudre le chemin des vues: " . $viewsPath);
      }

      if (!is_dir($viewsPath)) {
         if (!mkdir($viewsPath, 0755, true)) {
            throw new \RuntimeException("Impossible de créer le répertoire des vues: " . $viewsPath);
         }
      }

      $loader = new FilesystemLoader($viewsPath);
      $cachePath = storage_path('cache/twig');

      if (!is_dir($cachePath)) {
         if (!Filesystem::makeDirectory($cachePath, 0777, true)) {
            throw new \RuntimeException("Impossible de créer le répertoire de cache: " . $cachePath);
         }
      }

      if (!is_writable($cachePath)) {
         throw new \RuntimeException("Le répertoire de cache n'est pas accessible en écriture: " . $cachePath);
      }

      $this->twig = new Environment($loader, [
         'cache' => $cachePath,
         'auto_reload' => true,
         'debug' => true,
         'strict_variables' => true,
         'charset' => 'UTF-8'
      ]);

      $this->twig->addGlobal('APP_VERSION', config('app.version'));
      $this->twig->addGlobal('APP_LOCALE', config('app.locale'));

      $this->twig->addFunction(new \Twig\TwigFunction('component', function (string $name, $props = []) {
         return ComponentManager::render($name, is_array($props) ? $props : [$props]);
      }, ['is_safe' => ['html']]));

      $this->twig->addFunction(new \Twig\TwigFunction('dump', function (mixed $data) {
         return dump($data);
      }, ['is_safe' => ['html']]));

      $this->twig->addExtension(new ViteExtension());
      $this->twig->addExtension(new RouteExtension());
      $this->twig->addExtension(new AuthExtension());
      $this->twig->addExtension(new CustomFilterExtension());

      $this->twig->addGlobal('session', $_SESSION);
   }

   public static function getInstance(): self
   {
      return new self(view_path());
   }
   public function render(string $template, array $data = []): string
   {
      try {
         $template = ltrim($template, '.');
         $templatePath = !str_contains($template, '.')
            ? $template . '.twig'
            : str_replace('.', '/', $template) . '.twig';

         if (!$this->twig->getLoader()->exists($templatePath)) {
            throw new LoaderError("Le template n'existe pas: " . $templatePath);
         }

         return $this->twig->render($templatePath, array_merge($this->globalData, $data));
      } catch (\Exception $e) {
         throw new \RuntimeException("Erreur lors du rendu du template: " . $e->getMessage(), 0, $e);
      }
   }

   public function addGlobal(string $name, mixed $value): void
   {
      $this->twig->addGlobal($name, $value);
      $this->globalData[$name] = $value;
   }

   public function addFilter(string $name, callable $filter): void
   {
      $this->twig->addFilter(new \Twig\TwigFilter($name, $filter));
   }

   public function addFunction(string $name, callable $function): void
   {
      $this->twig->addFunction(new \Twig\TwigFunction($name, $function));
   }

   public function exists(string $template): bool
   {
      $template = ltrim($template, '.');
      $templatePath = !str_contains($template, '.')
         ? $template . '.twig'
         : str_replace('.', '/', $template) . '.twig';

      return $this->twig->getLoader()->exists($templatePath);
   }
}
