<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

use IronFlow\Cache\Contracts\CacheDriverInterface;
use IronFlow\Cache\Exceptions\CacheException;
use Redis;

/**
 * Pilote de cache basé sur Redis
 */
class RedisDriver implements CacheDriverInterface
{
   private Redis $redis;

   /**
    * @param array $config Configuration du pilote
    * @throws CacheException Si la connexion à Redis échoue
    */
   public function __construct(
      private readonly array $config
   ) {
      $this->connect();
   }

   /**
    * Récupère une valeur du cache
    *
    * @param string $key Clé à récupérer
    * @return mixed Valeur associée à la clé ou null si non trouvée
    */
   public function get(string $key): mixed
   {
      $value = $this->redis->get($key);

      if ($value === false) {
         return null;
      }

      return unserialize($value);
   }

   /**
    * Stocke une valeur dans le cache
    *
    * @param string $key Clé à stocker
    * @param mixed $value Valeur à stocker
    * @param int|null $ttl Durée de vie en secondes (null = pour toujours)
    * @return bool Succès de l'opération
    */
   public function set(string $key, mixed $value, ?int $ttl = null): bool
   {
      $serialized = serialize($value);

      if ($ttl === null) {
         return $this->redis->set($key, $serialized);
      }

      return $this->redis->setex($key, $ttl, $serialized);
   }

   /**
    * Vérifie si une clé existe dans le cache
    *
    * @param string $key Clé à vérifier
    * @return bool True si la clé existe
    */
   public function has(string $key): bool
   {
      return $this->redis->exists($key);
   }

   /**
    * Supprime une clé du cache
    *
    * @param string $key Clé à supprimer
    * @return bool Succès de l'opération
    */
   public function delete(string $key): bool
   {
      return $this->redis->del($key) > 0;
   }

   /**
    * Vide tout le cache
    *
    * @return bool Succès de l'opération
    */
   public function clear(): bool
   {
      return $this->redis->flushDB();
   }

   /**
    * Incrémente une valeur numérique
    *
    * @param string $key Clé à incrémenter
    * @param int $value Valeur à ajouter
    * @return int Nouvelle valeur
    */
   public function increment(string $key, int $value = 1): int
   {
      return $this->redis->incrBy($key, $value);
   }

   /**
    * Décrémente une valeur numérique
    *
    * @param string $key Clé à décrémenter
    * @param int $value Valeur à soustraire
    * @return int Nouvelle valeur
    */
   public function decrement(string $key, int $value = 1): int
   {
      return $this->redis->decrBy($key, $value);
   }

   /**
    * Établit la connexion à Redis
    *
    * @throws CacheException Si la connexion échoue
    */
   private function connect(): void
   {
      $this->redis = new Redis();

      try {
         $this->redis->connect(
            $this->config['host'],
            $this->config['port'] ?? 6379,
            $this->config['timeout'] ?? 0
         );

         if (isset($this->config['password']) && $this->config['password'] !== null) {
            $this->redis->auth($this->config['password']);
         }

         if (isset($this->config['database']) && $this->config['database'] !== null) {
            $this->redis->select($this->config['database']);
         }
      } catch (\RedisException $e) {
         throw new CacheException("Impossible de se connecter à Redis: {$e->getMessage()}");
      }
   }
}
