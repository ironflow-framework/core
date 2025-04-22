<?php

declare(strict_types=1);

namespace IronFlow\Security;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Security\Exceptions\SecurityException;

class SecurityManager
{
   private static ?SecurityManager $instance = null;
   private array $config;

   private function __construct()
   {
      $this->config = config('security', [
         'csrf_token_name' => '_token',
         'csrf_expire' => 7200,
         'xss_clean' => true,
         'content_security_policy' => true,
         'allowed_hosts' => ['localhost', '127.0.0.1'],
         'secure_headers' => true
      ]);
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function validateCsrfToken(Request $request): bool
   {
      if ($this->isExemptFromCsrf($request)) {
         return true;
      }

      $token = $request->input($this->config['csrf_token_name']);
      if (!$token || $token !== session()->get('csrf_token')) {
         throw new SecurityException('Token CSRF invalide');
      }

      return true;
   }

   public function generateCsrfToken(): string
   {
      $token = bin2hex(random_bytes(32));
      session()->set('csrf_token', $token);
      session()->set('csrf_token_expire', time() + $this->config['csrf_expire']);
      return $token;
   }

   public function cleanXSS(string $value): string
   {
      if (!$this->config['xss_clean']) {
         return $value;
      }

      return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
   }

   public function addSecurityHeaders(Response $response): void
   {
      if (!$this->config['secure_headers']) {
         return;
      }

      $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
      $response->headers->set('X-XSS-Protection', '1; mode=block');
      $response->headers->set('X-Content-Type-Options', 'nosniff');
      $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

      if ($this->config['content_security_policy']) {
         $response->headers->set('Content-Security-Policy', $this->getContentSecurityPolicy());
      }
   }

   public function validateHost(Request $request): bool
   {
      $host = $request->getHost();
      if (!in_array($host, $this->config['allowed_hosts'])) {
         throw new SecurityException('Hôte non autorisé');
      }
      return true;
   }

   private function isExemptFromCsrf(Request $request): bool
   {
      return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']) ||
         str_starts_with($request->path(), 'api/');
   }

   private function getContentSecurityPolicy(): string
   {
      return "default-src 'self'; " .
         "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
         "style-src 'self' 'unsafe-inline'; " .
         "img-src 'self' data: https:; " .
         "font-src 'self' data: https:; " .
         "connect-src 'self';";
   }

   public function sanitizeInput(array $input): array
   {
      $sanitized = [];
      foreach ($input as $key => $value) {
         if (is_array($value)) {
            $sanitized[$key] = $this->sanitizeInput($value);
         } else {
            $sanitized[$key] = $this->cleanXSS((string) $value);
         }
      }
      return $sanitized;
   }

   public function validatePassword(string $password): bool
   {
      return strlen($password) >= 8 &&
         preg_match('/[A-Z]/', $password) &&
         preg_match('/[a-z]/', $password) &&
         preg_match('/[0-9]/', $password) &&
         preg_match('/[^A-Za-z0-9]/', $password);
   }
}
