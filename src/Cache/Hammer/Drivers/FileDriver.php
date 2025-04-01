<?php

declare(strict_types=1);

namespace IronFlow\Cache\Hammer\Drivers;

use DateTime;
use IronFlow\Cache\Hammer\Contracts\CacheDriverInterface;
use IronFlow\Support\Facades\Filesystem;

/**
 * Driver de cache utilisant le système de fichiers
 */
class FileDriver implements CacheDriverInterface
{
    /**
     * Chemin vers le répertoire de cache
     * @var string
     */
    private string $cachePath;

    /**
     * Constructeur
     * @param string|null $cachePath Chemin vers le répertoire de cache (null pour utiliser le chemin par défaut)
     */
    public function __construct(?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? dirname(__DIR__, 4) . '/storage/cache';
        if (!Filesystem::isDirectory($this->cachePath)) {
            Filesystem::makeDirectory($this->cachePath, 0777, true);
        }
    }

    /**
     * Vérifie si une clé existe dans le cache
     * @param string $key La clé à vérifier
     * @return bool True si la clé existe, false sinon
     */
    public function has(string $key): bool
    {
        $path = $this->getPath($key);
        
        if (!file_exists($path)) {
            return false;
        }
        
        // Vérifier si la clé est expirée
        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }
        
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return false;
        }
        
        // Si la clé est expirée, on la supprime et on retourne false
        if (isset($data['expiration']) && $data['expiration'] !== null) {
            $expiration = new DateTime($data['expiration']);
            if (new DateTime() > $expiration) {
                $this->delete($key);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Récupère une valeur du cache
     * @param string $key La clé à récupérer
     * @return mixed La valeur stockée ou null si non trouvée
     */
    public function get(string $key): mixed
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return null;
        }
        
        // Vérifier si la clé est expirée
        if (isset($data['expiration']) && $data['expiration'] !== null) {
            $expiration = new DateTime($data['expiration']);
            if (new DateTime() > $expiration) {
                $this->delete($key);
                return null;
            }
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
        $path = $this->getPath($key);
        
        // Si un TTL est spécifié, calculer la date d'expiration
        $expiration = null;
        if ($ttl !== null && $ttl > 0) {
            $expiration = (new DateTime())->modify("+{$ttl} seconds")->format('Y-m-d H:i:s');
            
            // Si la valeur est un tableau et contient déjà une expiration, on la remplace
            if (is_array($value) && isset($value['expiration'])) {
                $value['expiration'] = $expiration;
                $expiration = null; // Pour éviter de l'ajouter deux fois
            }
        }
        
        // Si la valeur n'est pas un tableau ou ne contient pas d'expiration, on l'ajoute
        if ($expiration !== null && (!is_array($value) || !isset($value['expiration']))) {
            $value = [
                'value' => $value,
                'expiration' => $expiration
            ];
        }
        
        return file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Supprime une valeur du cache
     * @param string $key La clé à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete(string $key): bool
    {
        $path = $this->getPath($key);
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    /**
     * Met à jour la durée de vie d'une clé
     * @param string $key La clé à mettre à jour
     * @param int $ttl Nouvelle durée de vie en secondes
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function ttl(string $key, int $ttl): bool
    {
        $data = $this->get($key);
        if ($data === null) {
            return false;
        }
        
        // Calculer la nouvelle date d'expiration
        $expiration = (new DateTime())->modify("+{$ttl} seconds")->format('Y-m-d H:i:s');
        
        // Mettre à jour l'expiration
        if (is_array($data) && isset($data['value'])) {
            $data['expiration'] = $expiration;
        } else {
            $data = [
                'value' => $data,
                'expiration' => $expiration
            ];
        }
        
        return $this->set($key, $data);
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
        $data = $this->get($key);
        
        if ($data !== null) {
            return $data['value'] ?? $data;
        }
        
        $value = $callback();
        $this->set($key, [
            'value' => $value,
            'expiration' => $ttl ? (new DateTime())->modify("+{$ttl} seconds")->format('Y-m-d H:i:s') : null
        ]);
        
        return $value;
    }

    /**
     * Vide complètement le cache
     * @return bool True si le vidage a réussi, false sinon
     */
    public function flush(): bool
    {
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    /**
     * Génère le chemin du fichier de cache pour une clé
     * @param string $key La clé
     * @return string Le chemin du fichier
     */
    private function getPath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}
