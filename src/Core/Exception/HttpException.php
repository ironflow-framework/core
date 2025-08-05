<?php

declare(strict_types=1);

namespace IronFlow\Core\Exception;

class HttpException extends BaseException
{
    protected $message = 'HTTP Exception occurred';
    protected $code = 500;

    public function __construct(string $message = "", int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatusCode(): int
    {
        return $this->code;
    }
}