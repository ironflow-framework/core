<?php

declare(strict_types=1);

namespace IronFlow\Hammer\Drivers;

use DateTime;
use Redis;
use RedisException;
use IronFlow\Hammer\Contracts\CacheDriverInterface;

/**
 * Driver de cache utilisant Redis
 */
class RedisDriver implements CacheDriverInterface
{
    /**
     * Instance Redis
     * @var Redis
     */
    private Redis $redis;

    /**
     * Préfixe pour les clés de cache
     * @var string
     */
    private string $prefix;

    /**
     * Constructeur
     * @param array $config Configuration Redis (host, port, password, prefix)
     * @throws RedisException Si la connexion à Redis échoue
     */
    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'ironflow_cache:';
        
        $this->redis = new Redis();
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 0.0;
        $retry_interval = $config['retry_interval'] ?? 0;
        
        if (!$this->redis->connect($host, $port, $timeout, null, $retry_interval)) {
            throw new RedisException("Impossible de se connecter au serveur Redis: {$host}:{$port}");
        }
        
        if (isset($config['password']) && !empty($config['password'])) {
            $this->redis->auth($config['password']);
        }
        
        if (isset($config['database']) && is_numeric($config['database'])) {
            $this->redis->select((int)$config['database']);
        }
    }

    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key La clé à vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool
    {
        return (bool)$this->redis->exists($this->prefixKey($key));
    }

    /**
     * Récupère une valeur du cache
     * @param string $key La clé à récupérer
     * @return mixed La valeur stockée ou null si non trouvée
     */
    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->prefixKey($key));
        
        if ($value === false) {
            return null;
        }
        
        $data = json_decode($value, true);
        if (!is_array($data)) {
            return $value;
        }
        
        return $data;
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
        $prefixedKey = $this->prefixKey($key);
        $serialized = is_scalar($value) ? $value : json_encode($value);
        
        if ($ttl === null) {
            return (bool)$this->redis->set($prefixedKey, $serialized);
        }
        
        return (bool)$this->redis->setex($prefixedKey, $ttl, $serialized);
    }

    /**
     * Supprime une valeur du cache
     * @param string $key La clé à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete(string $key): bool
    {
        return (bool)$this->redis->del($this->prefixKey($key));
    }

    /**
     * Vide complètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        
        if (empty($keys)) {
            return true;
        }
        
        return (bool)$this->redis->del($keys);
    }

    /**
     * Met à jour la durée de vie d'une clé
     * @param string $key La clé à mettre à jour
     * @param int $ttl Nouvelle durée de vie en secondes
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool
    {
        return (bool)$this->redis->expire($this->prefixKey($key), $ttl);
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
