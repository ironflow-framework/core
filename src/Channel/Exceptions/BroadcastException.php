<?php

declare(strict_types=1);

namespace IronFlow\Channel\Exceptions;

use RuntimeException;

/**
 * Exception levée lors d'une erreur de diffusion sur un channel
 */
class BroadcastException extends RuntimeException
{
}
