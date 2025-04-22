<?php

declare(strict_types=1);

namespace IronFlow\Cache\Contracts;

/**
 * Interface pour les pilotes de cache
 */
interface CacheDriverInterface
{
   /**
    * Récupère une valeur du cache
    *
    * @param string $key Clé à récupérer
    * @return mixed Valeur associée à la clé ou null si non trouvée
    */
   public function get(string $key): mixed;

   /**
    * Stocke une valeur dans le cache
    *
    * @param string $key Clé à stocker
    * @param mixed $value Valeur à stocker
    * @param int|null $ttl Durée de vie en secondes (null = pour toujours)
    * @return bool Succès de l'opération
    */
   public function set(string $key, mixed $value, ?int $ttl = null): bool;

   /**
    * Vérifie si une clé existe dans le cache
    *
    * @param string $key Clé à vérifier
    * @return bool True si la clé existe
    */
   public function has(string $key): bool;

   /**
    * Supprime une clé du cache
    *
    * @param string $key Clé à supprimer
    * @return bool Succès de l'opération
    */
   public function delete(string $key): bool;

   /**
    * Vide tout le cache
    *
    * @return bool Succès de l'opération
    */
   public function clear(): bool;

   /**
    * Incrémente une valeur numérique
    *
    * @param string $key Clé à incrémenter
    * @param int $value Valeur à ajouter
    * @return int Nouvelle valeur
    */
   public function increment(string $key, int $value = 1): int;

   /**
    * Décrémente une valeur numérique
    *
    * @param string $key Clé à décrémenter
    * @param int $value Valeur à soustraire
    * @return int Nouvelle valeur
    */
   public function decrement(string $key, int $value = 1): int;
}
