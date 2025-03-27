<?php

declare(strict_types=1);

namespace IronFlow\Auth\Guards;

use IronFlow\Database\Model;
use IronFlow\Auth\OAuth\OAuthManager;
use League\OAuth2\Client\Provider\AbstractProvider;

class OAuthGuard implements GuardInterface
{
    private ?Model $user = null;
    private array $config;
    private string $userModel;
    private OAuthManager $oauthManager;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->userModel = $config['model'];
        $this->oauthManager = OAuthManager::getInstance();
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
        session()->remove('auth_user_id');
        session()->remove('auth_provider');
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

    protected function findOrCreateUser($oauthUser, string $provider): Model
    {
        $email = $oauthUser->getEmail();
        $model = $this->createModel();
        
        $user = $model->where('email', $email)->first();
        if ($user) {
            return $user;
        }

        return $model->create([
            'name' => $oauthUser->getName(),
            'email' => $email,
            'password' => bcrypt(str_random(16)),
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
}
