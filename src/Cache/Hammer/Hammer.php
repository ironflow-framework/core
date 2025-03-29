<?php

declare(strict_types=1);

namespace IronFlow\Cache\Hammer;

use DateTime;
use IronFlow\Hammer\Contracts\CacheDriverInterface;
use IronFlow\Hammer\Drivers\FileDriver;

/**
 * Classe principale du système de cache Hammer
 * Implémente le pattern Singleton pour assurer une instance unique
 */
class Hammer
{
    private static ?Hammer $instance = null;
    private CacheDriverInterface $driver;

    /**
     * Constructeur privé pour empêcher l'instanciation directe
     * @param CacheDriverInterface|null $driver Driver de cache à utiliser
     */
    private function __construct(?CacheDriverInterface $driver = null)
    {
        $this->driver = $driver ?? new FileDriver();
    }

    /**
     * Récupère l'instance unique de Hammer
     * @return self L'instance de Hammer
     */
    public static function getInstance(?CacheDriverInterface $driver = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($driver);
        } elseif ($driver !== null) {
            self::$instance->driver = $driver;
        }
        return self::$instance;
    }

    /**
     * Change le driver de cache utilisé
     * @param CacheDriverInterface $driver Nouveau driver à utiliser
     * @return self L'instance de Hammer
     */
    public function setDriver(CacheDriverInterface $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Récupère une valeur du cache
     * @param string $key Clé à récupérer
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur récupérée ou la valeur par défaut
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->driver->get($key);
        
        if ($item === null) {
            return $default;
        }

        if (isset($item['expiration']) && $item['expiration'] !== null && new DateTime() > new DateTime($item['expiration'])) {
            $this->driver->delete($key);
            return $default;
        }

        return $item['value'] ?? $default;
    }

    /**
     * Stocke une valeur dans le cache
     * @param string $key Clé pour stocker la valeur
     * @param mixed $value Valeur à stocker
     * @param int $minutes Durée de vie en minutes (0 pour pas d'expiration)
     * @return bool True si le stockage a réussi, false sinon
     */
    public function put(string $key, mixed $value, int $minutes = 0): bool
    {
        $expiration = $minutes > 0 ? (new DateTime())->modify("+{$minutes} minutes")->format('Y-m-d H:i:s') : null;
        
        return $this->driver->set($key, [
            'value' => $value,
            'expiration' => $expiration
        ]);
    }

    /**
     * Stocke une valeur dans le cache sans expiration
     * @param string $key Clé pour stocker la valeur
     * @param mixed $value Valeur à stocker
     * @return bool True si le stockage a réussi, false sinon
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value);
    }

    /**
     * Supprime une valeur du cache
     * @param string $key Clé à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function forget(string $key): bool
    {
        return $this->driver->delete($key);
    }

    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key Clé à vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Incrémente une valeur numérique dans le cache
     * @param string $key Clé à incrémenter
     * @param int $value Valeur à ajouter
     * @return int Nouvelle valeur
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    /**
     * Décrémente une valeur numérique dans le cache
     * @param string $key Clé à décrémenter
     * @param int $value Valeur à soustraire
     * @return int Nouvelle valeur
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    /**
     * Récupère une valeur du cache ou l'enregistre si elle n'existe pas
     * @param string $key Clé à récupérer
     * @param int $minutes Durée de vie en minutes
     * @param callable $callback Fonction à exécuter pour générer la valeur si non trouvée
     * @return mixed La valeur stockée ou générée
     */
    public function remember(string $key, int $minutes, callable $callback): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $minutes);
        return $value;
    }

    /**
     * Récupère une valeur du cache ou l'enregistre sans expiration si elle n'existe pas
     * @param string $key Clé à récupérer
     * @param callable $callback Fonction à exécuter pour générer la valeur si non trouvée
     * @return mixed La valeur stockée ou générée
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);
        return $value;
    }

    /**
     * Récupère une valeur du cache et la supprime
     * @param string $key Clé à récupérer et supprimer
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur récupérée ou la valeur par défaut
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    /**
     * Vide complètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool
    {
        return $this->driver->flush();
    }

    /**
     * Met à jour la durée de vie d'une clé
     * @param string $key Clé à mettre à jour
     * @param int $minutes Nouvelle durée de vie en minutes
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function touch(string $key, int $minutes): bool
    {
        $value = $this->get($key);
        if ($value === null) {
            return false;
        }
        
        return $this->put($key, $value, $minutes);
    }
}
