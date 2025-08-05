<?php

declare(strict_types=1);

namespace IronFlow\Core\Event;

/**
 * Simple Event Dispatcher (Observer pattern)
 */
class EventDispatcher
{
    protected array $listeners = [];

    public function listen(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, ...$payload): void
    {
        if (!empty($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $listener(...$payload);
            }
        }
    }

    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]);
    }
}
