<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

use Redis;
use Exception;

class RedisDriver implements CacheDriverInterface
{
    private Redis $redis;

    public function __construct(array $config = [])
    {
        $this->redis = new Redis();
        
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 0;
        $password = $config['password'] ?? null;
        
        if (!$this->redis->connect($host, $port, $timeout)) {
            throw new Exception("Could not connect to Redis server");
        }
        
        if ($password !== null) {
            if (!$this->redis->auth($password)) {
                throw new Exception("Could not authenticate with Redis server");
            }
        }
    }

    public function get(string $key): ?array
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function set(string $key, array $value): bool
    {
        return $this->redis->set($key, json_encode($value));
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function flush(): bool
    {
        return $this->redis->flushDB();
    }
}
