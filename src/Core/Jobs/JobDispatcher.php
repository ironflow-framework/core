<?php

namespace IronFlow\Core\Jobs;

use IronFlow\Core\Jobs\Concerns\JobInterface;

/**
 * Simple Job Dispatcher (synchronisé, extensible pour async)
 */
class JobDispatcher
{
    protected array $queue = [];

    public function dispatch(JobInterface $job): void
    {
        $this->queue[] = $job;
        $this->runJob($job);
    }

    protected function runJob(JobInterface $job): void
    {
        $job->handle();
    }

    // Pour une vraie queue asynchrone, il suffirait d'implémenter un système de workers/processus
}
