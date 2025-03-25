<?php

declare(strict_types=1);

namespace IronFlow\Cache;

class FileCacheDriver implements CacheDriver
{
   private string $path;

   public function __construct(array $config)
   {
      $this->path = $config['path'] ?? storage_path('framework/cache/data');

      if (!is_dir($this->path)) {
         mkdir($this->path, 0755, true);
      }
   }

   public function get(string $key, $default = null)
   {
      $file = $this->getFilePath($key);

      if (!file_exists($file)) {
         return $default;
      }

      $data = unserialize(file_get_contents($file));

      if ($data['expires_at'] && $data['expires_at'] < time()) {
         $this->forget($key);
         return $default;
      }

      return $data['value'];
   }

   public function put(string $key, $value, ?int $ttl = null): bool
   {
      $file = $this->getFilePath($key);
      $data = [
         'value' => $value,
         'expires_at' => $ttl ? time() + $ttl : null
      ];

      return file_put_contents($file, serialize($data)) !== false;
   }

   public function forget(string $key): bool
   {
      $file = $this->getFilePath($key);

      if (file_exists($file)) {
         return unlink($file);
      }

      return true;
   }

   public function flush(): bool
   {
      $files = glob($this->path . '/*');

      foreach ($files as $file) {
         if (is_file($file)) {
            unlink($file);
         }
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

   protected function getFilePath(string $key): string
   {
      return $this->path . '/' . md5($key) . '.cache';
   }
}
