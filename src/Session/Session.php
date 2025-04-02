<?php

declare(strict_types=1);

namespace IronFlow\Session;

use IronFlow\Support\Facades\Config;

class Session
{
   /**
    * Indique si la session a été démarrée
    */
   private bool $started = false;

   /**
    * Les données de la session
    */
   private array $data = [];

   /**
    * Constructeur
    */
   public function __construct()
   {
      if (session_status() === PHP_SESSION_NONE) {
         $this->start();
      }
   }

   /**
    * Démarre la session
    */
   public function start(): bool
   {
      if ($this->started) {
         return true;
      }

      $config = Config::get('session', []);

      if (isset($config['cookie_secure'])) {
         ini_set('session.cookie_secure', $config['cookie_secure']);
      }

      if (isset($config['cookie_httponly'])) {
         ini_set('session.cookie_httponly', $config['cookie_httponly']);
      }

      if (isset($config['cookie_samesite'])) {
         ini_set('session.cookie_samesite', $config['cookie_samesite']);
      }

      $this->started = session_start();

      if ($this->started) {
         $this->data = $_SESSION;
      }

      return $this->started;
   }

   /**
    * Définit une valeur dans la session
    */
   public function set(string $key, mixed $value): void
   {
      $this->data[$key] = $value;
      $_SESSION[$key] = $value;
   }

   /**
    * Récupère une valeur de la session
    */
   public function get(string $key, mixed $default = null): mixed
   {
      return $this->data[$key] ?? $default;
   }

   /**
    * Vérifie si une clé existe dans la session
    */
   public function has(string $key): bool
   {
      return isset($this->data[$key]);
   }

   /**
    * Supprime une valeur de la session
    */
   public function remove(string $key): void
   {
      unset($this->data[$key], $_SESSION[$key]);
   }

   /**
    * Vide la session
    */
   public function clear(): void
   {
      $this->data = [];
      $_SESSION = [];
   }

   /**
    * Génère un nouveau token CSRF
    */
   public function generateToken(): string
   {
      $token = bin2hex(random_bytes(32));
      $this->set('_token', $token);
      return $token;
   }

   /**
    * Récupère le token CSRF actuel
    */
   public function getToken(): ?string
   {
      return $this->get('_token');
   }

   /**
    * Vérifie si la session est active
    */
   public function isStarted(): bool
   {
      return $this->started;
   }

   /**
    * Récupère l'ID de la session
    */
   public function getId(): string
   {
      return session_id();
   }

   /**
    * Régénère l'ID de la session
    */
   public function regenerate(bool $deleteOldSession = true): bool
   {
      return session_regenerate_id($deleteOldSession);
   }

   /**
    * Détruit la session
    */
   public function destroy(): bool
   {
      $this->clear();
      $this->started = false;
      return session_destroy();
   }
}
