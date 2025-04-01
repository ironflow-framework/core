<?php

declare(strict_types=1);

namespace IronFlow\Cache\Hammer;

use InvalidArgumentException;
use IronFlow\Cache\Hammer\Contracts\CacheDriverInterface;
use IronFlow\Cache\Hammer\Drivers\FileDriver;
use IronFlow\Cache\Hammer\Drivers\RedisDriver;
use IronFlow\Cache\Hammer\Drivers\MemcachedDriver;

/**
 * Gestionnaire de cache pour le système Hammer
 * Permet de gérer plusieurs drivers de cache
 */
class HammerManager
{
    /**
     * Configuration du système de cache
     * @var array
     */
    private array $config;
    
    /**
     * Driver de cache par défaut
     * @var string
     */
    private string $defaultDriver;
    
    /**
     * Instances des drivers de cache
     * @var array<string, CacheDriverInterface>
     */
    private array $drivers = [];

    /**
     * Constructeur
     * @param array $config Configuration du systuème de cache
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->defaultDriver = $config['default'] ?? 'file';
    }

    /**
     * Définit le driver de cache par défaut
     * @param string $driver Nom du driver
     * @return void
     */
    public function setDefaultDriver(string $driver): void
    {
        $this->defaultDriver = $driver;
    }

    /**
     * Récupuère une instance de driver de cache
     * @param string|null $name Nom du driver (null pour utiliser le driver par défaut)
     * @return CacheDriverInterface Instance du driver
     */
    public function driver(?string $name = null): CacheDriverInterface
    {
        $name = $name ?: $this->defaultDriver;

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Crée une instance de driver de cache
     * @param string $name Nom du driver
     * @return CacheDriverInterface Instance du driver
     * @throws InvalidArgumentException Si le driver n'est pas supporté
     */
    protected function createDriver(string $name): CacheDriverInterface
    {
        $config = $this->config['stores'][$name] ?? [];

        return match ($name) {
            'file' => new FileDriver($config['path'] ?? null),
            'redis' => new RedisDriver($config),
            'memcached' => new MemcachedDriver($config),
            default => throw new InvalidArgumentException("Driver de cache non supporté: {$name}")
        };
    }

    /**
     * Récupuère une valeur du cache
     * @param string $key Clé u00e0 récupérer
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur récupérée ou la valeur par défaut
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->driver()->get($key);
        
        if ($item === null) {
            return $default;
        }

        return $item['value'] ?? $default;
    }

    /**
     * Stocke une valeur dans le cache
     * @param string $key Clé pour stocker la valeur
     * @param mixed $value Valeur u00e0 stocker
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return bool True si le stockage a réussi, false sinon
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->driver()->set($key, [
            'value' => $value,
            'expiration' => $ttl ? date('Y-m-d H:i:s', time() + $ttl) : null
        ]);
    }

    /**
     * Supprime une valeur du cache
     * @param string $key Clé u00e0 supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function forget(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    /**
     * Vide compluètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool
    {
        return $this->driver()->flush();
    }

    /**
     * Récupuère une valeur du cache ou l'enregistre si elle n'existe pas
     * @param string $key Clé u00e0 récupérer
     * @param callable $callback Fonction u00e0 exécuter pour générer la valeur si non trouvée
     * @param int|null $ttl Durée de vie en secondes (null pour pas d'expiration)
     * @return mixed La valeur stockée ou générée
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->driver()->remember($key, $callback, $ttl);
    }

    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key Clé u00e0 vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * Met u00e0 jour la durée de vie d'une clé
     * @param string $key Clé u00e0 mettre u00e0 jour
     * @param int $ttl Nouvelle durée de vie en secondes
     * @return bool True si la mise u00e0 jour a réussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool
    {
        return $this->driver()->ttl($key, $ttl);
    }
}
