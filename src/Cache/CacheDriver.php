<?php

declare(strict_types=1);

namespace IronFlow\Cache;

interface CacheDriver
{
   public function get(string $key, $default = null);
   public function put(string $key, $value, ?int $ttl = null): bool;
   public function forget(string $key): bool;
   public function flush(): bool;
   public function remember(string $key, callable $callback, ?int $ttl = null);
}
