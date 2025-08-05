<?php

declare(strict_types=1);

namespace IronFlow\Core\Provider\Concerns;

use IronFlow\Core\Container\Container;

interface ServiceProviderInterface
{
    public function register(Container $container): void;
    public function boot(Container $container): void;
}
