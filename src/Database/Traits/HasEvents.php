<?php

namespace IronFlow\Database\Traits;

trait HasEvents
{
    protected static array $events = [];

    public static function created(callable $callback): void
    {
        static::registerEvent('created', $callback);
    }

    public static function creating(callable $callback): void
    {
        static::registerEvent('creating', $callback);
    }

    public static function updated(callable $callback): void
    {
        static::registerEvent('updated', $callback);
    }

    public static function updating(callable $callback): void
    {
        static::registerEvent('updating', $callback);
    }

    public static function deleted(callable $callback): void
    {
        static::registerEvent('deleted', $callback);
    }

    public static function deleting(callable $callback): void
    {
        static::registerEvent('deleting', $callback);
    }

    protected static function registerEvent(string $event, callable $callback): void
    {
        if (!isset(static::$events[$event])) {
            static::$events[$event] = [];
        }
        static::$events[$event][] = $callback;
    }

    protected function fireEvent(string $event): void
    {
        if (isset(static::$events[$event])) {
            foreach (static::$events[$event] as $callback) {
                $callback($this);
            }
        }
    }
}
