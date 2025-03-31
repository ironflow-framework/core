<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface {}
