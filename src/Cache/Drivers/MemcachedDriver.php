<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

use IronFlow\Cache\Contracts\CacheDriverInterface;
use IronFlow\Cache\Exceptions\CacheException;
use Memcached;

/**
 * Pilote de cache basé sur Memcached
 */
class MemcachedDriver implements CacheDriverInterface
{
   private Memcached $memcached;

   /**
    * @param array $config Configuration du pilote
    * @throws CacheException Si la connexion à Memcached échoue
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
      $value = $this->memcached->get($key);

      if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
         return null;
      }

      return $value;
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
      if ($ttl === null) {
         return $this->memcached->set($key, $value);
      }

      return $this->memcached->set($key, $value, $ttl);
   }

   /**
    * Vérifie si une clé existe dans le cache
    *
    * @param string $key Clé à vérifier
    * @return bool True si la clé existe
    */
   public function has(string $key): bool
   {
      $this->memcached->get($key);
      return $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
   }

   /**
    * Supprime une clé du cache
    *
    * @param string $key Clé à supprimer
    * @return bool Succès de l'opération
    */
   public function delete(string $key): bool
   {
      return $this->memcached->delete($key);
   }

   /**
    * Vide tout le cache
    *
    * @return bool Succès de l'opération
    */
   public function clear(): bool
   {
      return $this->memcached->flush();
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
      $result = $this->memcached->increment($key, $value);
      return $result !== false ? $result : 0;
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
      $result = $this->memcached->decrement($key, $value);
      return $result !== false ? $result : 0;
   }

   /**
    * Établit la connexion à Memcached
    *
    * @throws CacheException Si la connexion échoue
    */
   private function connect(): void
   {
      $this->memcached = new Memcached();

      try {
         $servers = $this->config['servers'] ?? [['127.0.0.1', 11211]];

         foreach ($servers as $server) {
            $this->memcached->addServer(
               $server[0],
               $server[1] ?? 11211,
               $server[2] ?? 0
            );
         }

         if (isset($this->config['options'])) {
            $this->memcached->setOptions($this->config['options']);
         }
      } catch (\Exception $e) {
         throw new CacheException("Impossible de se connecter à Memcached: {$e->getMessage()}");
      }
   }
}
