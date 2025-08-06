<?php

declare(strict_types= 1);

namespace IronFlow\Core\Provider;

use IronFlow\Core\Provider\Concerns\ServiceProviderInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Enregistre les services du provider
     */
    abstract public function register(): void;

    /**
     * Bootstraps the provider services
     */
    abstract public function boot(): void;
}