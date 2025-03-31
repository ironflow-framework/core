<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Support\Facades\Flash;
use IronFlow\View\TwigView;

class Response
{
   private static ?TwigView $view = null;
   private string $content;
   private int $statusCode;
   private array $headers;
   private array $data = [];

   /**
    * Crée une nouvelle instance de Response
    *
    * @param string $content Le contenu de la réponse
    * @param int $statusCode Le code HTTP
    * @param array $headers Les en-têtes HTTP
    */
   public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
   {
      $this->content = $content;
      $this->statusCode = $statusCode;
      $this->headers = array_merge([
         'Content-Type' => 'text/html; charset=UTF-8'
      ], $headers);
   }

   /**
    * Définit le moteur de rendu de vue
    *
    * @param TwigView $view
    * @return void
    */
   public static function setView(TwigView $view): void
   {
      self::$view = $view;
   }

   /**
    * Crée une réponse de vue
    *
    * @param string $template Le template à rendre
    * @param array $data Les données à passer au template
    * @param int $statusCode Le code HTTP
    * @param array $headers Les en-têtes HTTP
    * @return self
    */
   public static function view(string $template, array $data = [], int $statusCode = 200, array $headers = []): self
   {
      if (self::$view === null) {
         throw new \RuntimeException('View engine not initialized. Call Response::setView() first.');
      }

      $content = self::$view->render($template, $data);
      return new self($content, $statusCode, $headers);
   }

   /**
    * Crée une réponse JSON
    *
    * @param array|object $data Les données à encoder en JSON
    * @param int $statusCode Le code HTTP
    * @param array $headers En-têtes HTTP supplémentaires
    * @return self
    */
   public static function json(array|object $data, int $statusCode = 200, array $headers = []): self
   {
      $headers = array_merge(['Content-Type' => 'application/json'], $headers);

      return new self(
         json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
         $statusCode,
         $headers
      );
   }

   /**
    * Crée une réponse de redirection
    *
    * @param string $url L'URL de redirection
    * @param int $statusCode Le code HTTP de redirection
    * @param array $headers En-têtes HTTP supplémentaires
    * @return self
    */
   public static function redirect(string $url, int $statusCode = 302, array $headers = []): self
   {
      $headers = array_merge(['Location' => $url], $headers);
      return new self('', $statusCode, $headers);
   }

   /**
    * Crée une réponse de téléchargement
    *
    * @param string $path Chemin du fichier à télécharger
    * @param string|null $name Nom du fichier téléchargé
    * @param array $headers En-têtes HTTP supplémentaires
    * @return self
    */
   public static function download(string $path, ?string $name = null, array $headers = []): self
   {
      if (!file_exists($path)) {
         throw new \RuntimeException("File not found: {$path}");
      }

      $name = $name ?? basename($path);
      $mime = mime_content_type($path) ?: 'application/octet-stream';

      $headers = array_merge([
         'Content-Type' => $mime,
         'Content-Disposition' => 'attachment; filename="' . $name . '"',
         'Content-Length' => (string)filesize($path),
      ], $headers);

      return new self(file_get_contents($path), 200, $headers);
   }

   /**
    * Crée une réponse pour afficher un fichier
    *
    * @param string $path Chemin du fichier à afficher
    * @param string|null $contentType Type MIME du fichier
    * @param array $headers En-têtes HTTP supplémentaires
    * @return self
    */
   public static function file(string $path, ?string $contentType = null, array $headers = []): self
   {
      if (!file_exists($path)) {
         throw new \RuntimeException("File not found: {$path}");
      }

      $contentType = $contentType ?? mime_content_type($path) ?: 'application/octet-stream';

      $headers = array_merge([
         'Content-Type' => $contentType,
         'Content-Length' => (string)filesize($path),
      ], $headers);

      return new self(file_get_contents($path), 200, $headers);
   }

   /**
    * Crée une réponse avec un statut HTTP
    *
    * @param int $statusCode Code HTTP
    * @param string $message Message optionnel
    * @return self
    */
   public static function status(int $statusCode, string $message = ''): self
   {
      return new self($message, $statusCode);
   }

   /**
    * Crée une réponse "Not Found" (404)
    *
    * @param string $message Message optionnel
    * @return self
    */
   public static function notFound(string $message = 'Not Found'): self
   {
      return self::status(404, $message);
   }

   /**
    * Crée une réponse "Forbidden" (403)
    *
    * @param string $message Message optionnel
    * @return self
    */
   public static function forbidden(string $message = 'Forbidden'): self
   {
      return self::status(403, $message);
   }

   /**
    * Crée une réponse "Unauthorized" (401)
    *
    * @param string $message Message optionnel
    * @return self
    */
   public static function unauthorized(string $message = 'Unauthorized'): self
   {
      return self::status(401, $message);
   }

   /**
    * Crée une réponse "Bad Request" (400)
    *
    * @param string $message Message optionnel
    * @return self
    */
   public static function badRequest(string $message = 'Bad Request'): self
   {
      return self::status(400, $message);
   }

   /**
    * Crée une réponse "Internal Server Error" (500)
    *
    * @param string $message Message optionnel
    * @return self
    */
   public static function serverError(string $message = 'Internal Server Error'): self
   {
      return self::status(500, $message);
   }

   /**
    * Définit le contenu de la réponse
    *
    * @param string $content
    * @return self
    */
   public function setContent(string $content): self
   {
      $this->content = $content;
      return $this;
   }

   /**
    * Récupère le contenu de la réponse
    *
    * @return string
    */
   public function getContent(): string
   {
      return $this->content;
   }

   /**
    * Définit le code HTTP de la réponse
    *
    * @param int $statusCode
    * @return self
    */
   public function setStatusCode(int $statusCode): self
   {
      $this->statusCode = $statusCode;
      return $this;
   }

   /**
    * Récupère le code HTTP de la réponse
    *
    * @return int
    */
   public function getStatusCode(): int
   {
      return $this->statusCode;
   }

   /**
    * Définit un en-tête HTTP
    *
    * @param string $name
    * @param string $value
    * @return self
    */
   public function setHeader(string $name, string $value): self
   {
      $this->headers[$name] = $value;
      return $this;
   }

   /**
    * Définit plusieurs en-têtes HTTP
    *
    * @param array $headers
    * @return self
    */
   public function withHeaders(array $headers): self
   {
      $this->headers = array_merge($this->headers, $headers);
      return $this;
   }

   /**
    * Récupère tous les en-têtes HTTP
    *
    * @return array
    */
   public function getHeaders(): array
   {
      return $this->headers;
   }

   /**
    * Envoie la réponse au client
    *
    * @return void
    */
   public function send(): void
   {
      if (!headers_sent()) {
         http_response_code($this->statusCode);
         foreach ($this->headers as $name => $value) {
            header("$name: $value");
         }
      }

      echo $this->content;
   }

   /**
    * Stocke une donnée en session flash
    *
    * @param string $key
    * @param mixed $value
    * @return self
    */
   public function with(string $key, $value): self
   {
      session()->flash($key, $value);
      return $this;
   }

   /**
    * Stocke un message de succès en session flash
    *
    * @param string $message
    * @return self
    */
   public function withSuccess(string $message): self
   {
      flash('success', $message);
      return $this;
   }

   /**
    * Stocke un message d'erreur en session flash
    *
    * @param string $message
    * @return self
    */
   public function withErrors(array $errors): self
   {
      flash('errors', $errors);
      return $this;
   }

   /**
    * Stocke un message d'information en session flash
    *
    * @param string $message
    * @return self
    */
   public function withInfo(string $message): self
   {
      flash('info', $message);
      return $this;
   }

   /**
    * Stocke un message d'avertissement en session flash
    *
    * @param string $message
    * @return self
    */
   public function withWarning(string $message): self
   {
      flash('warning', $message);
      return $this;
   }

   /**
    * Stocke les données de la requête en session flash
    *
    * @return self
    */
   public function withInput(): self
   {
      flash('input', $_POST);
      return $this;
   }

   /**
    * Redirige vers l'URL précédente
    *
    * @param int $status
    * @param array $headers
    * @return static
    */
   public static function back(int $status = 302, array $headers = []): self
   {
      $previousUrl = $_SERVER['HTTP_REFERER'] ?? '/';
      return self::redirect($previousUrl, $status, $headers);
   }

   /**
    * Renvoie l'URL actuelle
    *
    * @return string
    */
   public function url(): string
   {
      return $_SERVER['REQUEST_URI'] ?? '/';
   }
}
