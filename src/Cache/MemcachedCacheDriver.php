<?php

declare(strict_types=1);

namespace IronFlow\Cache;

use Memcached;

class MemcachedCacheDriver implements CacheDriver
{
   private Memcached $memcached;
   private string $prefix;

   public function __construct(array $config)
   {
      $this->memcached = new Memcached($config['persistent_id'] ?? null);

      if (isset($config['sasl'])) {
         $this->memcached->setSaslAuthData($config['sasl'][0], $config['sasl'][1]);
      }

      if (isset($config['options'])) {
         $this->memcached->setOptions($config['options']);
      }

      $servers = $config['servers'] ?? [
         ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]
      ];

      $this->memcached->addServers($servers);
      $this->prefix = $config['prefix'] ?? 'ironflow_cache:';
   }

   public function get(string $key, $default = null)
   {
      $value = $this->memcached->get($this->prefix . $key);

      if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
         return $default;
      }

      return unserialize($value);
   }

   public function put(string $key, $value, ?int $ttl = null): bool
   {
      $key = $this->prefix . $key;
      $value = serialize($value);

      if ($ttl) {
         return $this->memcached->set($key, $value, time() + $ttl);
      }

      return $this->memcached->set($key, $value);
   }

   public function forget(string $key): bool
   {
      return $this->memcached->delete($this->prefix . $key);
   }

   public function flush(): bool
   {
      return $this->memcached->flush();
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
