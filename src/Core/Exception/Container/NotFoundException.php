<?php

declare(strict_types=1);

namespace IronFlow\Core\Exception\Container;

use IronFlow\Core\Exception\BaseException;

class NotFoundException extends BaseException
{
    protected $message = 'Service not found in the container';
    protected $code = 404;

    public function __construct(string $serviceName, int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Service "%s" not found in the container', $serviceName), $code, $previous);
    }
}