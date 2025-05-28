<?php 

declare(strict_types=1);

namespace IronFlow\Database\Migrations;



/**
 * Résultat d'une opération de migration
 */
class MigrationResult
{
    protected array $successful = [];
    protected array $errors = [];
    protected string $message;

    public function __construct(array $successful = [], string $message = '')
    {
        $this->successful = $successful;
        $this->message = $message;
    }

    public function addSuccess(string $migration): void
    {
        $this->successful[] = $migration;
    }

    public function addError(string $migration, \Throwable $error): void
    {
        $this->errors[$migration] = $error;
    }

    public function getSuccessful(): array
    {
        return $this->successful;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    public function getMessage(): string
    {
        if ($this->hasErrors()) {
            return sprintf(
                '%d migrations completed, %d failed',
                count($this->successful),
                count($this->errors)
            );
        }

        return $this->message ?: sprintf('%d migrations completed successfully', count($this->successful));
    }
}
