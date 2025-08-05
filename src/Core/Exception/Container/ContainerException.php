<?php

declare(strict_types= 1);

namespace IronFlow\Core\Exception\Container;

use IronFlow\Core\Exception\BaseException;

class ContainerException extends BaseException
{
    protected $message = 'Container exception occurred';
}