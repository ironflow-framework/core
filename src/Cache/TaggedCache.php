<?php

declare(strict_types=1);

namespace IronFlow\Cache;

/**
 * Classe pour gérer le cache avec des tags
 */
class TaggedCache
{
   /**
    * @param CacheManager $cache Instance du gestionnaire de cache
    * @param array $tags Tags associés au cache
    */
   public function __construct(
      private readonly CacheManager $cache,
      private readonly array $tags
   ) {}

   /**
    * Récupère une valeur du cache
    *
    * @param string $key Clé à récupérer
    * @param mixed $default Valeur par défaut si la clé n'existe pas
    * @return mixed Valeur associée à la clé ou la valeur par défaut
    */
   public function get(string $key, mixed $default = null): mixed
   {
      $taggedKey = $this->getTaggedKey($key);
      return $this->cache->get($taggedKey, $default);
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
      $taggedKey = $this->getTaggedKey($key);
      return $this->cache->set($taggedKey, $value, $ttl);
   }

   /**
    * Supprime une clé du cache
    *
    * @param string $key Clé à supprimer
    * @return bool Succès de l'opération
    */
   public function delete(string $key): bool
   {
      $taggedKey = $this->getTaggedKey($key);
      return $this->cache->delete($taggedKey);
   }

   /**
    * Vide tout le cache associé aux tags
    *
    * @return bool Succès de l'opération
    */
   public function flush(): bool
   {
      // Implémentation à faire selon la stratégie de tags
      return true;
   }

   /**
    * Génère une clé avec les tags
    *
    * @param string $key Clé originale
    * @return string Clé avec les tags
    */
   private function getTaggedKey(string $key): string
   {
      $tagString = implode('|', $this->tags);
      return "tag:{$tagString}:{$key}";
   }
}
