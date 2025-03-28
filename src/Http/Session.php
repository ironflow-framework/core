<?php

namespace IronFlow\Http;

class Session
{

  protected $session;

  public function __construct()
  {
    $this->session = $_SESSION;
  }

  public function flash(string $key, $value): void
  {
    $this->session[$key] = $value;
  }

  public function get(string $key, $default = null)
  {
    return $this->session[$key] ?? $default;
  }

  public function forget(string $key): void
  {
    unset($this->session[$key]);
  }

  public function clear(): void
  {
    $this->session = [];
  }

  public function all(): array
  {
    return $this->session;
  }

  public function has(string $key): bool
  {
    return isset($this->session[$key]);
  }

  public function regenerate(): void
  {
    session_regenerate_id(true);
    $this->session = $_SESSION;
  }

  public function getId(): string
  {
    return session_id();
  }

  public function getName(): string
  {
    return session_name();
  }

  public function isStarted(): bool
  {
    return session_status() === PHP_SESSION_ACTIVE;
  }

  public function start(): void
  {
    session_start();
    $this->session = $_SESSION;
  }

  public function destroy(): void
  {
    session_destroy();
    $this->session = [];
  }

  public function save(): void
  {
    $_SESSION = $this->session;
  }

  public function put(string $key, $value)
  {
    if ($this->has($key)) {
      $this->forget($key);
    }

    $this->flash($key, $value);
    
  }
  public function getMessages(): array
  {
    return $this->get('messages', []);
  }
}
