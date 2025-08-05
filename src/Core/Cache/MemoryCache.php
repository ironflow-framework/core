<?php

namespace IronFlow\Core\Cache;

/**
 * Simple in-memory cache (singleton, for fast access)
 */

use IronFlow\Core\Cache\CacheInterface;

class MemoryCache implements CacheInterface
{
    private static ?MemoryCache $instance = null;
    private array $store = [];
    private array $expires = [];

    private function __construct() {}

    public static function getInstance(): MemoryCache
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        if (!isset($this->store[$key])) return $default;
        if ($this->expires[$key] !== 0 && $this->expires[$key] < time()) {
            unset($this->store[$key], $this->expires[$key]);
            return $default;
        }
        return $this->store[$key];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->store[$key] = $value;
        $this->expires[$key] = $ttl ? time() + $ttl : 0;
        return true;
    }

    public function delete($key): bool
    {
        unset($this->store[$key], $this->expires[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = [];
        $this->expires = [];
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has($key): bool
    {
        if (!isset($this->store[$key])) return false;
        if ($this->expires[$key] !== 0 && $this->expires[$key] < time()) {
            unset($this->store[$key], $this->expires[$key]);
            return false;
        }
        return true;
    }
}
