<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Channel\ChannelManager;
use IronFlow\Channel\Events\ChannelEvent;

class Notification
{
    protected static ?ChannelManager $channel = null;

    public static function send(string $message, string $type = 'info', ?string $channel = 'notifications'): void
    {
        if (static::$channel === null) {
            static::$channel = app(ChannelManager::class);
        }

        static::$channel->broadcast($channel, new ChannelEvent('notification', ['message'=> $message, 'type' => $type]));

        // Stocker également dans la session pour la prochaine requête
        session()->flash($type, $message);
    }

    public static function success(string $message, ?string $channel = 'notifications'): void
    {
        static::send($message, 'success', $channel);
    }

    public static function error(string $message, ?string $channel = 'notifications'): void
    {
        static::send($message, 'error', $channel);
    }

    public static function info(string $message, ?string $channel = 'notifications'): void
    {
        static::send($message, 'info', $channel);
    }

    public static function warning(string $message, ?string $channel = 'notifications'): void
    {
        static::send($message, 'warning', $channel);
    }
}
