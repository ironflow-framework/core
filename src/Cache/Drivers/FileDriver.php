<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

use IronFlow\Cache\Contracts\CacheDriverInterface;
use IronFlow\Cache\Exceptions\CacheException;

/**
 * Pilote de cache basé sur le système de fichiers
 */
class FileDriver implements CacheDriverInterface
{
   /**
    * @param array $config Configuration du pilote
    */
   public function __construct(
      private readonly array $config
   ) {
      $this->ensureDirectoryExists();
   }

   /**
    * Récupère une valeur du cache
    *
    * @param string $key Clé à récupérer
    * @return mixed Valeur associée à la clé ou null si non trouvée
    */
   public function get(string $key): mixed
   {
      $path = $this->getFilePath($key);

      if (!file_exists($path)) {
         return null;
      }

      $data = file_get_contents($path);
      $item = unserialize($data);

      if ($item['expiration'] !== null && $item['expiration'] < time()) {
         $this->delete($key);
         return null;
      }

      return $item['value'];
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
      $path = $this->getFilePath($key);
      $expiration = $ttl !== null ? time() + $ttl : null;

      $item = [
         'value' => $value,
         'expiration' => $expiration,
      ];

      return file_put_contents($path, serialize($item)) !== false;
   }

   /**
    * Vérifie si une clé existe dans le cache
    *
    * @param string $key Clé à vérifier
    * @return bool True si la clé existe
    */
   public function has(string $key): bool
   {
      $path = $this->getFilePath($key);

      if (!file_exists($path)) {
         return false;
      }

      $data = file_get_contents($path);
      $item = unserialize($data);

      if ($item['expiration'] !== null && $item['expiration'] < time()) {
         $this->delete($key);
         return false;
      }

      return true;
   }

   /**
    * Supprime une clé du cache
    *
    * @param string $key Clé à supprimer
    * @return bool Succès de l'opération
    */
   public function delete(string $key): bool
   {
      $path = $this->getFilePath($key);

      if (file_exists($path)) {
         return unlink($path);
      }

      return true;
   }

   /**
    * Vide tout le cache
    *
    * @return bool Succès de l'opération
    */
   public function clear(): bool
   {
      $files = glob($this->config['path'] . '/*');

      foreach ($files as $file) {
         if (is_file($file)) {
            unlink($file);
         }
      }

      return true;
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
      $current = $this->get($key);

      if ($current === null) {
         $current = 0;
      } elseif (!is_numeric($current)) {
         throw new CacheException("La valeur pour la clé '{$key}' n'est pas numérique");
      }

      $new = $current + $value;
      $this->set($key, $new);

      return $new;
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
      return $this->increment($key, -$value);
   }

   /**
    * Vérifie que le répertoire de cache existe
    *
    * @throws CacheException Si le répertoire ne peut pas être créé
    */
   private function ensureDirectoryExists(): void
   {
      if (!is_dir($this->config['path'])) {
         if (!mkdir($this->config['path'], 0755, true)) {
            throw new CacheException("Impossible de créer le répertoire de cache: {$this->config['path']}");
         }
      }
   }

   /**
    * Obtient le chemin complet du fichier de cache
    *
    * @param string $key Clé du cache
    * @return string Chemin complet du fichier
    */
   private function getFilePath(string $key): string
   {
      return $this->config['path'] . '/' . md5($key) . '.cache';
   }
}
