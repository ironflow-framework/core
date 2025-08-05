<?php

namespace IronFlow\Core\Cache;

/**
 * Interface compatible PSR-16 pour le cache simple
 */
interface CacheInterface
{
    public function get($key, $default = null);
    public function set($key, $value, $ttl = null): bool;
    public function delete($key): bool;
    public function clear(): bool;
    public function getMultiple($keys, $default = null): iterable;
    public function setMultiple($values, $ttl = null): bool;
    public function deleteMultiple($keys): bool;
    public function has($key): bool;
}
