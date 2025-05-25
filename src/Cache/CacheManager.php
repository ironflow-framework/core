<?php

declare(strict_types=1);

namespace IronFlow\Cache;

use IronFlow\Cache\Drivers\FileDriver;
use IronFlow\Cache\Drivers\RedisDriver;
use IronFlow\Cache\Drivers\MemcachedDriver;
use IronFlow\Cache\Contracts\CacheDriverInterface;
use IronFlow\Cache\Exceptions\CacheException;

class CacheManager
{
   private static ?CacheManager $instance = null;
   private ?CacheDriverInterface $driver = null;
   private array $config;

   public function __construct()
   {
      $this->config = config('cache', [
         'default' => 'file',
         'ttl' => 3600,
         'stores' => [
            'file' => [
               'driver' => 'file',
               'path' => storage_path('cache'),
            ],
            'redis' => [
               'driver' => 'redis',
               'connection' => 'cache',
               'servers' => [
                  [
                     'host' => '127.0.0.1',
                     'port' => 6379,
                     'password' => null,
                     'database' => 0,
                     'timeout' => 10,
                  ],
               ],
               'lock_connection' => 'default',
            ],
            'memcached' => [
               'driver' => 'memcached',
               'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
               'sasl' => [
                  env('MEMCACHED_USERNAME'),
                  env('MEMCACHED_PASSWORD'),
               ],
               'options' => [
                  // Memcached::OPT_CONNECT_TIMEOUT => 2000,
               ],
               'servers' => [
                  [
                     'host' => '127.0.0.1',
                     'port' => 11211,
                     'weight' => 100,
                  ]
               ]
            ],
         ],

         'prefix' => 'ironflow_cache_',
      ]);

      $this->initializeDriver();
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   private function initializeDriver(): void
   {
      $driver = $this->config['default'];
 
      $this->driver = match ($driver) {
         'file' => new FileDriver($this->config['stores']['file']),
         'redis' => new RedisDriver($this->config['stores']['redis']),
         'memcached' => new MemcachedDriver($this->config['stores']['memcached']),
         default => throw new CacheException("Driver de cache non supporté: {$driver}")
      };
   }

   public function driver(): string
   {
      return $this->config['default'];
   }

   public function setDriver(string $driver): void
   {
      $this->driver = match ($driver) {
         'file' => new FileDriver($this->config['stores']['file']),
         'redis' => new RedisDriver($this->config['stores']['redis']),
         'memcached' => new MemcachedDriver($this->config['stores']['memcached']),
         default => throw new CacheException("Driver de cache non supporté: {$driver}")
      };
   }

   public function get(string $key, mixed $default = null): mixed
   {
      $value = $this->driver->get($this->prefix($key));
      return $value !== null ? $value : $default;
   }

   public function set(string $key, mixed $value, ?int $ttl = null): bool
   {
      return $this->driver->set(
         $this->prefix($key),
         $value,
         $ttl ?? $this->config['ttl']
      );
   }

   public function has(string $key): bool
   {
      return $this->driver->has($this->prefix($key));
   }

   public function delete(string $key): bool
   {
      return $this->driver->delete($this->prefix($key));
   }

   public function clear(): bool
   {
      return $this->driver->clear();
   }

   public function remember(string $key, callable $callback, ?int $ttl = null): mixed
   {
      if ($value = $this->get($key)) {
         return $value;
      }

      $value = $callback();
      $this->set($key, $value, $ttl);
      return $value;
   }

   public function tags(array $tags): TaggedCache
   {
      return new TaggedCache($this, $tags);
   }

   public function increment(string $key, int $value = 1): int
   {
      return $this->driver->increment($this->prefix($key), $value);
   }

   public function decrement(string $key, int $value = 1): int
   {
      return $this->driver->decrement($this->prefix($key), $value);
   }

   public function forever(string $key, mixed $value): bool
   {
      return $this->set($key, $value, 0);
   }

   private function prefix(string $key): string
   {
      return $this->config['prefix'] . $key;
   }
}
