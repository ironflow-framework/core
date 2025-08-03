<?php

namespace IronFlow\Core\Cache;



use IronFlow\Core\Cache\CacheInterface;

/**
 * Simple PSR-16 compatible file cache
 */
class FileCache implements CacheInterface
{
    protected string $cacheDir;
    protected int $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR);
        $this->defaultTtl = $defaultTtl;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    protected function getFile(string $key): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }

    public function get($key, $default = null)
    {
        $file = $this->getFile($key);
        if (!file_exists($file)) return $default;
        $data = unserialize(file_get_contents($file));
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            unlink($file);
            return $default;
        }
        return $data['value'];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $file = $this->getFile($key);
        $expires = $ttl ? time() + $ttl : ($this->defaultTtl ? time() + $this->defaultTtl : 0);
        $data = [
            'value' => $value,
            'expires' => $expires
        ];
        return file_put_contents($file, serialize($data)) !== false;
    }

    public function delete($key): bool
    {
        $file = $this->getFile($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.cache');
        $ok = true;
        foreach ($files as $file) {
            $ok = $ok && unlink($file);
        }
        return $ok;
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
        $ok = true;
        foreach ($values as $key => $value) {
            $ok = $ok && $this->set($key, $value, $ttl);
        }
        return $ok;
    }

    public function deleteMultiple($keys): bool
    {
        $ok = true;
        foreach ($keys as $key) {
            $ok = $ok && $this->delete($key);
        }
        return $ok;
    }

    public function has($key): bool
    {
        $file = $this->getFile($key);
        if (!file_exists($file)) return false;
        $data = unserialize(file_get_contents($file));
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            unlink($file);
            return false;
        }
        return true;
    }
}
