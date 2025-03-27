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
