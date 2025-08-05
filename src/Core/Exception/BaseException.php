<?php

declare(strict_types=1);

namespace IronFlow\Core\Exception;

class BaseException extends \Exception
{
    protected $message = 'An error occurred in the application';
    protected $code = 0;
    protected $previous;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorMessage(): string
    {
        return sprintf('%s (Code: %d)', $this->message, $this->code);
    }
}