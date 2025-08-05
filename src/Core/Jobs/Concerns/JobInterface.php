<?php

declare(strict_types=1);

namespace IronFlow\Core\Jobs\Concerns;

/**
 * Interface de base pour un Job
 */
interface JobInterface
{
    public function handle(): void;
}
