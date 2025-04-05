<?php

declare(strict_types=1);

namespace IronFlow\Http;

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
    * @param array<string, mixed> $session Les données de session
    */
   public function __construct(
      mixed $content = '',
      int $status = 200,
      array $headers = [],
      array $session = []
   ) {
      parent::__construct($content, $status, $headers);
      $this->session = $session;
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
         $this->session[$key] = $value;
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
      return $this->with(['success' => $data]);
   }

   /**
    * Ajouter des données d'erreur à la reponse
    *
    * @param mixed $data Les données a ajouté à la reponse d'erreur
    * @return static
    */
   public function withErrors(mixed $data): static
   {
      if (is_array($data)) {
         $errors = [];
         foreach ($data as $key => $value) {
            $errors[(string)$key] = $value;
         }
         return $this->with(['errors' => $errors]);
      }
      return $this->with(['errors' => $data]);
   }

   /**
    * Ajouter les anciennes données à la reponse
    *
    * @param array $old Les anciennes données
    * @return static
    */
   public function withOld(array $old): static
   {
      $formattedOld = [];
      foreach ($old as $key => $value) {
         $formattedOld[(string)$key] = $value;
      }
      return $this->with(['old' => $formattedOld]);
   }

   public function withInput(): self
   {
      return $this->withOld($_POST);
   }

   protected array $session = [];

   public function withSession(array $session): self
   {
      $this->session = array_merge($this->session, $session);
      return $this;
   }

   public function withFlash(string $key, mixed $value): static
   {
      return $this->with(['flash' => [$key => $value]]);
   }

   public function getSession(): array
   {
      return $this->session;
   }

   public function getHeaders(): array
   {
      return $this->headers->all();
   }

   public function send(): static
   {
      if (!headers_sent()) {
         http_response_code($this->getStatusCode());
         
         foreach ($this->getHeaders() as $name => $values) {
            if (is_array($values)) {
               foreach ($values as $value) {
                  header("$name: $value", false);
               }
            } else {
               header("$name: $values");
            }
         }

         foreach ($this->session as $key => $value) {
            $_SESSION[$key] = $value;
         }
      }

      if ($this->getContent() !== '') {
         if (is_string($this->getContent())) {
            echo $this->getContent();
         } elseif (is_array($this->getContent()) || is_object($this->getContent())) {
            echo json_encode($this->getContent());
         }
      }

      return $this;
   }
}
