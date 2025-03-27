<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\View\TwigView;

class Response
{
   private static ?TwigView $view = null;
   private string $content;
   private int $statusCode;
   private array $headers;
   private array $data;

   public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
   {
      $this->content = $content;
      $this->statusCode = $statusCode;
      $this->headers = array_merge([
         'Content-Type' => 'text/html; charset=UTF-8'
      ], $headers);
   }

   public static function setView(TwigView $view): void
   {
      self::$view = $view;
   }

   public static function view(string $template, array $data = [], int $statusCode = 200, array $headers = []): self
   {
      if (self::$view === null) {
         throw new \RuntimeException('View engine not initialized. Call Response::setView() first.');
      }

      $content = self::$view->render($template, $data);
      return new self($content, $statusCode, $headers);
   }

   public static function json(array $data, int $statusCode = 200): self
   {
      return new self(
         json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
         $statusCode,
         ['Content-Type' => 'application/json']
      );
   }

   public static function redirect(string $url, int $statusCode = 302): self
   {
      return new self('', $statusCode, ['Location' => $url]);
   }

   public function setContent(string $content): self
   {
      $this->content = $content;
      return $this;
   }

   public function setStatusCode(int $statusCode): self
   {
      $this->statusCode = $statusCode;
      return $this;
   }

   public function setHeader(string $name, string $value): self
   {
      $this->headers[$name] = $value;
      return $this;
   }

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

   public function with(string $key, $value): self
   {
      $this->data[$key] = $value;
      return $this;
   }

   public function url()
   {
      return $_SERVER['REQUEST_URI'];
   }
}
