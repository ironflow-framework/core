<?php

declare(strict_types=1);

namespace IronFlow\Auth;

use IronFlow\Auth\Contracts\GuardInterface;
use IronFlow\Auth\Guards\SessionGuard;
use IronFlow\Auth\Guards\TokenGuard;
use IronFlow\Database\Model;

class AuthManager
{
    private static ?AuthManager $instance = null;
    private ?GuardInterface $guard = null;
    private array $config;

    public function __construct(array $config = [])
    {
        $defaultGuard = config('auth.defaults.guard');

        $this->config = array_merge([
            'guard' => config('auth.guards')[$defaultGuard]['driver'],
            'model' => config('auth.providers.users.model'),
            'session_key' => 'auth_user_id',
            'token_key' => 'api_token',
        ], $config);

        $this->setGuard($this->config['guard']);
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function setGuard(string $guard): void
    {
        $this->guard = match ($guard) {
            'session' => new SessionGuard($this->config),
            'token' => new TokenGuard($this->config),
            default => throw new \InvalidArgumentException("Unsupported guard type: {$guard}")
        };
    }

    public function attempt(array $credentials): bool
    {
        return $this->guard->attempt($credentials);
    }

    public function login(Model $user): void
    {
        $this->guard->login($user);
    }

    public function logout(): void
    {
        $this->guard->logout();
    }

    public function check(): bool
    {
        return $this->guard->check();
    }

    public function user(): Model|null
    {
        return $this->guard->user();
    }

    public function id(): ?int
    {
        return $this->guard->id();
    }

    public function validate(array $credentials): bool
    {
        return $this->guard->validate($credentials);
    }
}
