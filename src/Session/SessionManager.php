<?php

declare(strict_types=1);

namespace IronFlow\Session;

use IronFlow\Session\Drivers\FileSessionHandler;
use IronFlow\Session\Drivers\RedisSessionHandler;
use IronFlow\Session\Exceptions\SessionException;

class SessionManager
{
   private static ?SessionManager $instance = null;
   private array $config;
   private bool $started = false;

   private function __construct()
   {
      $this->config = config('session', [
         'driver' => 'file',
         'lifetime' => 120,
         'expire_on_close' => false,
         'encrypt' => false,
         'files' => storage_path('framework/sessions'),
         'connection' => null,
         'table' => 'sessions',
         'lottery' => [2, 100],
         'cookie' => 'ironflow_session',
         'path' => '/',
         'domain' => null,
         'secure' => true,
         'http_only' => true,
         'same_site' => 'lax',
      ]);

      $this->start();
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function start(): bool
   {
      if ($this->started) {
         return true;
      }

      $handler = $this->createHandler();
      session_set_save_handler($handler, true);

      $this->configure();

      if (session_status() === PHP_SESSION_NONE) {
         session_start();
      }

      $this->started = true;
      return true;
   }

   private function configure(): void
   {
      ini_set('session.gc_maxlifetime', (string) ($this->config['lifetime'] * 60));
      ini_set('session.gc_probability', (string) $this->config['lottery'][0]);
      ini_set('session.gc_divisor', (string) $this->config['lottery'][1]);

      session_set_cookie_params(
         $this->config['lifetime'] * 60,
         $this->config['path'],
         $this->config['domain'],
         $this->config['secure'],
         $this->config['http_only']
      );

      session_name($this->config['cookie']);
   }

   private function createHandler(): \SessionHandlerInterface
   {
      return match ($this->config['driver']) {
         'file' => new FileSessionHandler($this->config['files']),
         'redis' => new RedisSessionHandler($this->config['connection']),
         default => throw new SessionException("Driver de session non supporté: {$this->config['driver']}")
      };
   }

   public function get(string $key, mixed $default = null): mixed
   {
      return $_SESSION[$key] ?? $default;
   }

   public function set(string $key, mixed $value): void
   {
      $_SESSION[$key] = $value;
   }

   public function has(string $key): bool
   {
      return isset($_SESSION[$key]);
   }

   public function remove(string $key): void
   {
      unset($_SESSION[$key]);
   }

   public function clear(): void
   {
      session_unset();
   }

   public function destroy(): void
   {
      if ($this->started) {
         session_destroy();
         $this->started = false;
      }
   }

   public function regenerate(bool $deleteOldSession = false): bool
   {
      return session_regenerate_id($deleteOldSession);
   }

   public function all(): array
   {
      return $_SESSION;
   }

   public function flash(string $key, mixed $value): void
   {
      $_SESSION['_flash'][$key] = $value;
   }

   public function getFlash(string $key, mixed $default = null): mixed
   {
      if (isset($_SESSION['_flash'][$key])) {
         $value = $_SESSION['_flash'][$key];
         unset($_SESSION['_flash'][$key]);
         return $value;
      }
      return $default;
   }

   public function token(): string
   {
      if (!isset($_SESSION['_token'])) {
         $_SESSION['_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['_token'];
   }

   public function previousUrl(): ?string
   {
      return $_SESSION['_previous_url'] ?? null;
   }

   public function setPreviousUrl(string $url): void
   {
      $_SESSION['_previous_url'] = $url;
   }
}
