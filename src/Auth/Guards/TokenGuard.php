<?php

declare(strict_types=1);

namespace IronFlow\Auth\Guards;

use IronFlow\Auth\Contracts\GuardInterface;
use IronFlow\Database\Model;
use IronFlow\Http\Request;

class TokenGuard implements GuardInterface
{
    private ?Model $user = null;
    private array $config;
    private string $userModel;
    private string $tokenKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->userModel = $config['model'];
        $this->tokenKey = $config['token_key'];
    }

    public function attempt(array $credentials): bool
    {
        if ($this->validate($credentials)) {
            $user = $this->retrieveByCredentials($credentials);
            $this->login($user);
            return true;
        }
        return false;
    }

    public function login(Model $user): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?Model
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();
        if ($token === null) {
            return null;
        }

        $this->user = $this->retrieveByToken($token);
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user()?->id;
    }

    public function validate(array $credentials): bool
    {
        return isset($credentials[$this->tokenKey]) &&
            $this->retrieveByToken($credentials[$this->tokenKey]) !== null;
    }

    protected function retrieveByToken(string $token): ?Model
    {
        return $this->createModel()
            ->where($this->tokenKey, $token);
    }

    protected function retrieveByCredentials(array $credentials): ?Model
    {
        if (empty($credentials) || !isset($credentials[$this->tokenKey])) {
            return null;
        }

        return $this->retrieveByToken($credentials[$this->tokenKey]);
    }

    protected function getTokenFromRequest(): ?string
    {
        $request = new Request();

        // Check Authorization header
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        // Check query string
        return $request->query($this->tokenKey);
    }

    protected function createModel(): Model
    {
        $class = '\\' . ltrim($this->userModel, '\\');
        return new $class;
    }
}
