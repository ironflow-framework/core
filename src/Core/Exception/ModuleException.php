<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Exception;

class ModuleException extends BaseException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}