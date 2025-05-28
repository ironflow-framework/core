<?php

declare(strict_types=1);

namespace IronFlow\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Facebook;

class OAuthManager
{
    private static ?OAuthManager $instance = null;
    private array $config;
    private array $providers = [];

    private function __construct()
    {
        $this->config = config('oauth');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getProvider(string $name): AbstractProvider
    {
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $provider = $this->createProvider($name);
        $this->providers[$name] = $provider;
        return $provider;
    }

    public function attempt(array $credentials): bool
    {
        if (!isset($credentials['provider'], $credentials['code'])) {
            return false;
        }

        try {
            $provider = $this->oauthManager->getProvider($credentials['provider']);
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $credentials['code']
            ]);

            $oauthUser = $provider->getResourceOwner($token);
            $user = $this->findOrCreateUser($oauthUser, $credentials['provider']);

            $this->login($user);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function login(Model $user): void
    {
        $this->user = $user;
        session()->put('auth_user_id', $user->id);
        session()->put('auth_provider', 'oauth');
    }

    public function logout(): void
    {
        $this->user = null;
        session()->forget('auth_user_id');
        session()->forget('auth_provider');
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): Model|Collection|null
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $id = session()->get('auth_user_id');
        $provider = session()->get('auth_provider');

        if ($id === null || $provider !== 'oauth') {
            return null;
        }

        $this->user = $this->retrieveById($id);
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user()?->id;
    }

    public function validate(array $credentials): bool
    {
        return isset($credentials['provider'], $credentials['code']);
    }

    protected function findOrCreateUser($oauthUser, string $provider): Model|Collection
    {
        $email = $oauthUser->getEmail();
        $model = $this->createModel();

        $user = $model->where('email', $email)->get();
        if ($user) {
            return $user;
        }

        return $model::create([
            'name' => $oauthUser->getName(),
            'email' => $email,
            'password' => PasswordHasher::hash(Str::random(16)),
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
        ]);
    }

    protected function retrieveById(int $id): ?Model
    {
        return $this->createModel()->find($id);
    }

    protected function createModel(): Model
    {
        $class = '\\' . ltrim($this->userModel, '\\');
        return new $class;
    }

    protected function createProvider(string $name): AbstractProvider
    {
        $config = $this->getProviderConfig($name);

        return match ($name) {
            'google' => new Google([
                'clientId' => $config['client_id'],
                'clientSecret' => $config['client_secret'],
                'redirectUri' => $config['redirect_uri'],
            ]),
            'github' => new Github([
                'clientId' => $config['client_id'],
                'clientSecret' => $config['client_secret'],
                'redirectUri' => $config['redirect_uri'],
            ]),
            'facebook' => new Facebook([
                'clientId' => $config['client_id'],
                'clientSecret' => $config['client_secret'],
                'redirectUri' => $config['redirect_uri'],
                'graphApiVersion' => 'v12.0',
            ]),
            default => throw new \InvalidArgumentException("Unsupported OAuth provider: {$name}")
        };
    }

    protected function getProviderConfig(string $name): array
    {
        if (!isset($this->config['providers'][$name])) {
            throw new \InvalidArgumentException("OAuth provider {$name} is not configured");
        }

        return $this->config['providers'][$name];
    }
}
