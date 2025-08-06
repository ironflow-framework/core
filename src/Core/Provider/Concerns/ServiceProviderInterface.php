<?php

declare(strict_types=1);

namespace IronFlow\Core\Provider\Concerns;

interface ServiceProviderInterface
{
    public function register(): void;
    public function boot(): void;
}
