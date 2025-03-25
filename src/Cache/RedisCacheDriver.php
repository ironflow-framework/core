<?php

declare(strict_types=1);

namespace IronFlow\Cache;

use Redis;

class RedisCacheDriver implements CacheDriver
{
   private Redis $redis;
   private string $prefix;

   public function __construct(array $config)
   {
      $this->redis = new Redis();
      $this->redis->connect(
         $config['host'] ?? '127.0.0.1',
         $config['port'] ?? 6379
      );

      if (isset($config['password'])) {
         $this->redis->auth($config['password']);
      }

      if (isset($config['database'])) {
         $this->redis->select($config['database']);
      }

      $this->prefix = $config['prefix'] ?? 'ironflow_cache:';
   }

   public function get(string $key, $default = null)
   {
      $value = $this->redis->get($this->prefix . $key);

      if ($value === false) {
         return $default;
      }

      return unserialize($value);
   }

   public function put(string $key, $value, ?int $ttl = null): bool
   {
      $key = $this->prefix . $key;
      $value = serialize($value);

      if ($ttl) {
         return $this->redis->setex($key, $ttl, $value);
      }

      return $this->redis->set($key, $value);
   }

   public function forget(string $key): bool
   {
      return $this->redis->del($this->prefix . $key) > 0;
   }

   public function flush(): bool
   {
      $keys = $this->redis->keys($this->prefix . '*');

      if (!empty($keys)) {
         return $this->redis->del($keys) > 0;
      }

      return true;
   }

   public function remember(string $key, callable $callback, ?int $ttl = null)
   {
      $value = $this->get($key);

      if ($value !== null) {
         return $value;
      }

      $value = $callback();
      $this->put($key, $value, $ttl);

      return $value;
   }
}
