<?php

declare(strict_types=1);

namespace IronFlow\Cache\Hammer;

use InvalidArgumentException;
use IronFlow\Hammer\Contracts\CacheDriverInterface;
use IronFlow\Hammer\Drivers\FileDriver;
use IronFlow\Hammer\Drivers\RedisDriver;
use IronFlow\Hammer\Drivers\MemcachedDriver;

/**
 * Gestionnaire de cache pour le systu00e8me Hammer
 * Permet de gu00e9rer plusieurs drivers de cache
 */
class HammerManager
{
    /**
     * Configuration du systu00e8me de cache
     * @var array
     */
    private array $config;
    
    /**
     * Driver de cache par du00e9faut
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
     * @param array $config Configuration du systu00e8me de cache
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->defaultDriver = $config['default'] ?? 'file';
    }

    /**
     * Du00e9finit le driver de cache par du00e9faut
     * @param string $driver Nom du driver
     * @return void
     */
    public function setDefaultDriver(string $driver): void
    {
        $this->defaultDriver = $driver;
    }

    /**
     * Ru00e9cupu00e8re une instance de driver de cache
     * @param string|null $name Nom du driver (null pour utiliser le driver par du00e9faut)
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
     * Cru00e9e une instance de driver de cache
     * @param string $name Nom du driver
     * @return CacheDriverInterface Instance du driver
     * @throws InvalidArgumentException Si le driver n'est pas supportu00e9
     */
    protected function createDriver(string $name): CacheDriverInterface
    {
        $config = $this->config['stores'][$name] ?? [];

        return match ($name) {
            'file' => new FileDriver($config['path'] ?? null),
            'redis' => new RedisDriver($config),
            'memcached' => new MemcachedDriver($config),
            default => throw new InvalidArgumentException("Driver de cache non supportu00e9: {$name}")
        };
    }

    /**
     * Ru00e9cupu00e8re une valeur du cache
     * @param string $key Clu00e9 u00e0 ru00e9cupu00e9rer
     * @param mixed $default Valeur par du00e9faut si la clu00e9 n'existe pas
     * @return mixed La valeur ru00e9cupu00e9ru00e9e ou la valeur par du00e9faut
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
     * @param string $key Clu00e9 pour stocker la valeur
     * @param mixed $value Valeur u00e0 stocker
     * @param int|null $ttl Duru00e9e de vie en secondes (null pour pas d'expiration)
     * @return bool True si le stockage a ru00e9ussi, false sinon
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
     * @param string $key Clu00e9 u00e0 supprimer
     * @return bool True si la suppression a ru00e9ussi, false sinon
     */
    public function forget(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    /**
     * Vide complu00e8tement le cache
     * @return bool True si le vidage a ru00e9ussi, false sinon
     */
    public function flush(): bool
    {
        return $this->driver()->flush();
    }

    /**
     * Ru00e9cupu00e8re une valeur du cache ou l'enregistre si elle n'existe pas
     * @param string $key Clu00e9 u00e0 ru00e9cupu00e9rer
     * @param callable $callback Fonction u00e0 exu00e9cuter pour gu00e9nu00e9rer la valeur si non trouvu00e9e
     * @param int|null $ttl Duru00e9e de vie en secondes (null pour pas d'expiration)
     * @return mixed La valeur stocku00e9e ou gu00e9nu00e9ru00e9e
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->driver()->remember($key, $callback, $ttl);
    }

    /**
     * Vu00e9rifie si une clu00e9 existe dans le cache
     * @param string $key Clu00e9 u00e0 vu00e9rifier
     * @return bool True si la clu00e9 existe, false sinon
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * Met u00e0 jour la duru00e9e de vie d'une clu00e9
     * @param string $key Clu00e9 u00e0 mettre u00e0 jour
     * @param int $ttl Nouvelle duru00e9e de vie en secondes
     * @return bool True si la mise u00e0 jour a ru00e9ussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool
    {
        return $this->driver()->ttl($key, $ttl);
    }
}
