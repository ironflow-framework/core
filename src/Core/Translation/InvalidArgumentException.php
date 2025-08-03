<?php 

declare(strict_types=1);

namespace IronFlow\Core\Translation;

use IronFlow\Core\Exception\BaseException;

class InvalidArgumentException extends BaseException
{
    public function __construct(string $message = "Invalid argument provided for translation", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}