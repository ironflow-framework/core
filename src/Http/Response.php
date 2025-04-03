<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use IronFlow\View\TwigView;

/**
 * Classe de réponse HTTP
 * 
 * Cette classe encapsule une réponse HTTP.
 */
class Response extends HttpFoundationResponse
{
   /**
    * Crée une nouvelle instance de la réponse
    * 
    * @param mixed $content Le contenu de la réponse
    * @param int $status Le code de statut HTTP
    * @param array<string, string> $headers Les en-têtes de la réponse
    */
   public function __construct(
      mixed $content = '',
      int $status = 200,
      array $headers = []
   ) {
      parent::__construct($content, $status, $headers);
   }

   /**
    * Crée une réponse JSON
    * 
    * @param mixed $data Les données à encoder en JSON
    * @param int $status Le code de statut HTTP
    * @param array<string, string> $headers Les en-têtes de la réponse
    * @return static
    */
   public static function json(mixed $data, int $status = 200, array $headers = []): static
   {
      return new static(
         json_encode($data, JSON_THROW_ON_ERROR),
         $status,
         array_merge(['Content-Type' => 'application/json'], $headers)
      );
   }

   /**
    * Crée une réponse de redirection
    * 
    * @param string $url L'URL de redirection
    * @param int $status Le code de statut HTTP
    * @return static
    */
   public static function redirect(string $url, int $status = 302): static
   {
      return new static('', $status, ['Location' => $url]);
   }

   /**
    * Crée une réponse de fichier
    * 
    * @param string $file Le chemin du fichier
    * @param int $status Le code de statut HTTP
    * @param array<string, string> $headers Les en-têtes de la réponse
    * @return static
    */
   public static function file(string $file, int $status = 200, array $headers = []): static
   {
      $response = new static('', $status, $headers);
      $response->headers->set('Content-Type', mime_content_type($file));
      $response->headers->set('Content-Length', (string) filesize($file));
      $response->setContent(file_get_contents($file));
      return $response;
   }

   /**
    * Crée une réponse de flux de données
    * 
    * @param callable $callback La fonction de callback pour générer le contenu
    * @param int $status Le code de statut HTTP
    * @param array<string, string> $headers Les en-têtes de la réponse
    * @return static
    */
   public static function streamResponse(callable $callback, int $status = 200, array $headers = []): static
   {
      $response = new static('', $status, $headers);
      $response->headers->set('Content-Type', 'application/octet-stream');
      $response->setContent($callback());
      return $response;
   }

   /**
    * Crée une réponse de vue
    * 
    * @param string $view Le nom de la vue
    * @param array<string, mixed> $data Les données de la vue
    * @param int $status Le code de statut HTTP
    * @return static
    */
   public static function view(string $view, array $data = [], int $status = 200): static
   {
      $twig = new TwigView(view_path());
      $content = $twig->render($view, $data);
      return new static($content, $status);
   }

   /**
    * Ajouter des données à la reponse
    *
    * @param array<string, mixed> $data Les données a ajouté à la reponse
    * @return static
    */
   public function with(array $data): static
   {
      foreach ($data as $key => $value) {
         Session::set($key, $value);
      }

      return $this;
   }

   /**
    * Ajouter des données de succès à la reponse
    *
    * @param mixed $data Les données a ajouté à la reponse de succès
    * @return static
    */
   public function withSuccess(mixed $data): static
   {
      return $this->with(['success', $data]);
   }

   /**
    * Ajouter des données d'erreur à la reponse
    *
    * @param mixed $data Les données a ajouté à la reponse d'erreur
    * @return static
    */
   public function withErrors(mixed $data): static
   {
      return $this->with(['errors', $data]);
   }

   
   public function withOld(array $old): self
   {
      return $this->with(['old', $old]);
   }

   public function withInput(): self
   {
      return $this->withOld($_POST);
   }

}
