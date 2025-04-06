<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

/**
 * @method static void info(string $message, ?string $channel = 'notifications', array $options = [])
 * @method static void success(string $message, ?string $channel = 'notifications', array $options = [])
 * @method static void warning(string $message, ?string $channel = 'notifications', array $options = [])
 * @method static void error(string $message, ?string $channel = 'notifications', array $options = [])
 * @method static void send(string $message, string $type = 'info', ?string $channel = 'notifications', array $options = [])
 */
class Toast extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'toast';
    }
}
