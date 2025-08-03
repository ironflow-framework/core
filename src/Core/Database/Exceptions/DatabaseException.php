<?php

declare(strict_types=1);

namespace IronFlow\Core\Database\Exceptions;

use IronFlow\Core\Exception\BaseException;

/**
 * Exception de base pour les erreurs de base de données
 */
class DatabaseException extends BaseException
{
    protected array $context = [];

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Récupère le contexte de l'erreur
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Ajoute du contexte à l'exception
     */
    public function setContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }
}
