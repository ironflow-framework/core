<?php

declare(strict_types=1);

namespace IronFlow\Core\Providers\Concernes;

/**
 * Summary of ServiceProviderInterface
 */
interface ServiceProviderInterface
{
    public function register(): void;

    public function boot(): void;
}
