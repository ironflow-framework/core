<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

/**
 * Statut des migrations
 */
class MigrationStatus
{
    public function __construct(
        protected array $all,
        protected array $completed,
        protected array $pending
    ) {}

    public function getAllMigrations(): array
    {
        return $this->all;
    }

    public function getCompletedMigrations(): array
    {
        return $this->completed;
    }

    public function getPendingMigrations(): array
    {
        return $this->pending;
    }

    public function getTotalCount(): int
    {
        return count($this->all);
    }

    public function getCompletedCount(): int
    {
        return count($this->completed);
    }

    public function getPendingCount(): int
    {
        return count($this->pending);
    }

    public function isUpToDate(): bool
    {
        return empty($this->pending);
    }
}