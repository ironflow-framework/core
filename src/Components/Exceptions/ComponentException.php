<?php

declare(strict_types=1);

namespace IronFlow\Components\Exceptions;

use Exception;
use Throwable;

class ComponentException extends Exception
{
    /**
     * Constructor.
     *
     * @param string         $message  The Exception message to throw.
     * @param int            $code     The Exception code.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function componentNotFound(string $component): self
    {
        return new self("Le composant '{$component}' est introuvable.");
    }
}
