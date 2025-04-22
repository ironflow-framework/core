<?php

declare(strict_types=1);

namespace IronFlow\Session\Drivers;

use SessionHandlerInterface;

/**
 * Interface pour les opérations Redis nécessaires
 */
interface RedisInterface
{
   public function get(string $key): string|false;
   public function setex(string $key, int $ttl, string $value): bool;
   public function del(string $key): int;
}

/**
 * Gestionnaire de session basé sur Redis
 */
class RedisSessionHandler implements SessionHandlerInterface
{
   /**
    * Instance Redis
    */
   private RedisInterface $redis;

   /**
    * Préfixe des clés de session
    */
   private string $prefix;

   /**
    * Durée de vie des sessions en secondes
    */
   private int $ttl;

   /**
    * Constructeur
    *
    * @param RedisInterface $redis Instance Redis
    * @param string $prefix Préfixe des clés
    * @param int $ttl Durée de vie en secondes
    */
   public function __construct(RedisInterface $redis, string $prefix = 'session:', int $ttl = 3600)
   {
      $this->redis = $redis;
      $this->prefix = $prefix;
      $this->ttl = $ttl;
   }

   /**
    * {@inheritdoc}
    */
   public function open(string $path, string $name): bool
   {
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function close(): bool
   {
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function read(string $id): string|false
   {
      $data = $this->redis->get($this->prefix . $id);
      return $data === false ? '' : $data;
   }

   /**
    * {@inheritdoc}
    */
   public function write(string $id, string $data): bool
   {
      return $this->redis->setex($this->prefix . $id, $this->ttl, $data);
   }

   /**
    * {@inheritdoc}
    */
   public function destroy(string $id): bool
   {
      return $this->redis->del($this->prefix . $id) > 0;
   }

   /**
    * {@inheritdoc}
    */
   public function gc(int $max_lifetime): int|false
   {
      // Redis gère automatiquement l'expiration des clés
      return 0;
   }
}
