<?php

declare(strict_types=1);

namespace IronFlow\Core\Provider;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Event\EventDispatcher;
use IronFlow\Core\Provider\Concerns\ServiceProviderInterface;

/**
 * Gestionnaire de service providers
 */
final class ProviderManager
{
    private array $providers = [];
    private array $booted = [];

    public function __construct(
        private Container $container,
        private EventDispatcher $events
    ) {}

    public function register(string|ServiceProviderInterface $provider): void
    {
        if (is_string($provider)) {
            $provider = new $provider();
        }

        $providerClass = get_class($provider);
        
        if (isset($this->providers[$providerClass])) {
            return;
        }

        $this->providers[$providerClass] = $provider;
        
        $this->events->dispatch('provider.registering', $provider);
        $provider->register();
        $this->events->dispatch('provider.registered', $provider);
    }

    public function bootProviders(): void
    {
        foreach ($this->providers as $providerClass => $provider) {
            if (!isset($this->booted[$providerClass])) {
                $this->events->dispatch('provider.booting', $provider);
                $provider->boot();
                $this->booted[$providerClass] = true;
                $this->events->dispatch('provider.booted', $provider);
            }
        }
    }
}