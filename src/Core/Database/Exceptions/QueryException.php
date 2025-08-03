<?php

declare(strict_types=1);

namespace IronFlow\Core\Database\Exceptions;

/**
 * Exception pour les erreurs de requête
 */
class QueryException extends DatabaseException
{
    protected string $sql = '';
    protected array $bindings = [];

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $sql = '', array $bindings = [])
    {
        parent::__construct($message, $code, $previous);
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * Récupère la requête SQL
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Récupère les bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Récupère la requête complète avec les bindings
     */
    public function getFullSql(): string
    {
        $sql = $this->sql;
        foreach ($this->bindings as $binding) {
            $value = is_string($binding) ? "'{$binding}'" : (string) $binding;
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}
