<?php

declare(strict_types=1);

namespace IronFlow\Hammer\Contracts;

interface CacheDriverInterface
{
    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key La clé à vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool;

    /**
     * Récupère une valeur du cache
     * @param string $key La clé à récupérer
     * @return mixed La valeur stockée ou null si non trouvée
     */
    public function get(string $key): mixed;
    
    /**
     * Stocke une valeur dans le cache
     * @param string $key La clé pour stocker la valeur
     * @param mixed $value La valeur à stocker
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return bool True si le stockage a réussi, false sinon
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    
    /**
     * Supprime une valeur du cache
     * @param string $key La clé à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete(string $key): bool;
    
    /**
     * Vide complètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool;

    /**
     * Met à jour la durée de vie d'une clé
     * @param string $key La clé à mettre à jour
     * @param int $ttl Nouvelle durée de vie en secondes
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool;

    /**
     * Récupère une valeur du cache ou l'enregistre si elle n'existe pas
     * @param string $key La clé à récupérer
     * @param callable $callback Fonction à exécuter pour générer la valeur si non trouvée
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return mixed La valeur stockée ou générée
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed;
}
