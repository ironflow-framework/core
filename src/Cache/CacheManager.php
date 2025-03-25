<?php

declare(strict_types=1);

namespace IronFlow\Cache;

class CacheManager
{
   private array $config;
   private string $defaultDriver;
   private array $drivers = [];

   public function __construct(array $config)
   {
      $this->config = $config;
      $this->defaultDriver = $config['default'] ?? 'file';
   }

   public function setDefaultDriver(string $driver): void
   {
      $this->defaultDriver = $driver;
   }

   public function driver(?string $name = null): CacheDriver
   {
      $name = $name ?: $this->defaultDriver;

      if (!isset($this->drivers[$name])) {
         $this->drivers[$name] = $this->createDriver($name);
      }

      return $this->drivers[$name];
   }

   protected function createDriver(string $name): CacheDriver
   {
      $config = $this->config['stores'][$name] ?? [];

      return match ($name) {
         'file' => new FileCacheDriver($config),
         'redis' => new RedisCacheDriver($config),
         'memcached' => new MemcachedCacheDriver($config),
         default => throw new \InvalidArgumentException("Driver de cache non supporté: {$name}")
      };
   }

   public function get(string $key, $default = null)
   {
      return $this->driver()->get($key, $default);
   }

   public function put(string $key, $value, ?int $ttl = null): bool
   {
      return $this->driver()->put($key, $value, $ttl);
   }

   public function forget(string $key): bool
   {
      return $this->driver()->forget($key);
   }

   public function flush(): bool
   {
      return $this->driver()->flush();
   }

   public function remember(string $key, callable $callback, ?int $ttl = null)
   {
      return $this->driver()->remember($key, $callback, $ttl);
   }
}
