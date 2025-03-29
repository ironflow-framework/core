<?php

declare(strict_types=1);

namespace IronFlow\Cache\Hammer\Drivers;

use DateTime;
use Memcached;
use IronFlow\Hammer\Contracts\CacheDriverInterface;

/**
 * Driver de cache utilisant Memcached
 */
class MemcachedDriver implements CacheDriverInterface
{
    /**
     * Instance Memcached
     * @var Memcached
     */
    private Memcached $memcached;

    /**
     * Préfixe pour les clés de cache
     * @var string
     */
    private string $prefix;

    /**
     * Constructeur
     * @param array $config Configuration Memcached (servers, prefix)
     */
    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'ironflow_cache:';
        
        $this->memcached = new Memcached($config['persistent_id'] ?? 'ironflow');
        
        // Ajouter les serveurs seulement s'ils ne sont pas déjà configurés
        if (empty($this->memcached->getServerList())) {
            $servers = $config['servers'] ?? [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]
            ];
            
            foreach ($servers as $server) {
                $this->memcached->addServer(
                    $server['host'] ?? '127.0.0.1',
                    $server['port'] ?? 11211,
                    $server['weight'] ?? 100
                );
            }
        }
        
        // Configuration des options
        if (isset($config['options']) && is_array($config['options'])) {
            $this->memcached->setOptions($config['options']);
        }
    }

    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key La clé à vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool
    {
        return $this->memcached->get($this->prefixKey($key)) !== false || 
               $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }

    /**
     * Récupère une valeur du cache
     * @param string $key La clé à récupérer
     * @return mixed La valeur stockée ou null si non trouvée
     */
    public function get(string $key): mixed
    {
        $value = $this->memcached->get($this->prefixKey($key));
        
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            return null;
        }
        
        return $value;
    }

    /**
     * Stocke une valeur dans le cache
     * @param string $key La clé pour stocker la valeur
     * @param mixed $value La valeur à stocker
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return bool True si le stockage a réussi, false sinon
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->memcached->set(
            $this->prefixKey($key),
            $value,
            $ttl ?? 0
        );
    }

    /**
     * Supprime une valeur du cache
     * @param string $key La clé à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->prefixKey($key));
    }

    /**
     * Vide complètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * Met à jour la durée de vie d'une clé
     * @param string $key La clé à mettre à jour
     * @param int $ttl Nouvelle durée de vie en secondes
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool
    {
        $prefixedKey = $this->prefixKey($key);
        $value = $this->memcached->get($prefixedKey);
        
        if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
            return false;
        }
        
        return $this->memcached->set($prefixedKey, $value, $ttl);
    }

    /**
     * Récupère une valeur du cache ou l'enregistre si elle n'existe pas
     * @param string $key La clé à récupérer
     * @param callable $callback Fonction à exécuter pour générer la valeur si non trouvée
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return mixed La valeur stockée ou générée
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * Préfixe une clé de cache
     * @param string $key La clé à préfixer
     * @return string La clé préfixée
     */
    private function prefixKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
