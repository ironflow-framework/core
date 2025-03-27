<?php

declare(strict_types=1);

namespace IronFlow\Cache;

use DateTime;
use IronFlow\Cache\Contracts\CacheDriverInterface;
use IronFlow\Cache\Drivers\FileDriver;

class Cache
{
    private static ?Cache $instance = null;
    private CacheDriverInterface $driver;

    private function __construct(?CacheDriverInterface $driver = null)
    {
        $this->driver = $driver ?? new FileDriver();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->driver->get($key);
        
        if ($item === null) {
            return $default;
        }

        if ($item['expiration'] !== null && new DateTime() > new DateTime($item['expiration'])) {
            $this->driver->delete($key);
            return $default;
        }

        return $item['value'];
    }

    public function put(string $key, mixed $value, int $minutes = 0): bool
    {
        $expiration = $minutes > 0 ? (new DateTime())->modify("+{$minutes} minutes")->format('Y-m-d H:i:s') : null;
        
        return $this->driver->set($key, [
            'value' => $value,
            'expiration' => $expiration
        ]);
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value);
    }

    public function forget(string $key): bool
    {
        return $this->driver->delete($key);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    public function remember(string $key, int $minutes, callable $callback): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $minutes);
        return $value;
    }

    public function rememberForever(string $key, callable $callback): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);
        return $value;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    public function flush(): bool
    {
        return $this->driver->flush();
    }
}
