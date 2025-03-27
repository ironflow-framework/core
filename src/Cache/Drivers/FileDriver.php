<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

use IronFlow\Cache\Contracts\CacheDriverInterface;

class FileDriver implements CacheDriverInterface
{
    private string $cachePath;

    public function __construct(?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? dirname(__DIR__, 4) . '/storage/cache';
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    public function has(string $key): bool
    {
        $path = $this->getPath($key);

        if (file_exists($path)) {
            return true;
        }

        return false;
    }

    public function get(string $key): ?array
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return json_decode($content, true);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $path = $this->getPath($key);
        return file_put_contents($path, json_encode($value)) !== false;
    }

    public function delete(string $key): bool
    {
        $path = $this->getPath($key);
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    public function ttl(string $key, int $ttl): bool
    {
        // TODO: Implementer la methode
        return true;
    }

    public function remember(string $key, callable $callback, int|null $ttl = null)
    {
        // TODO: Implementer la methode
    }

    public function flush(): bool
    {
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    private function getPath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}
