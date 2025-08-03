<?php

declare(strict_types=1);

namespace IronFlow\Core\Http;

/**
 * HTTP Request - Encapsule une requête HTTP
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    private array $routeParams = [];
    public $data;

    /**
     * Crée une requête à partir des globales PHP
     */
    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE);
    }

    /**
     * Obtient la méthode HTTP
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Obtient l'URI de la requête
     */
    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';

        // Enlever la query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }

    /**
     * Obtient un paramètre de query string
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Obtient un paramètre de requête (POST/PUT/PATCH)
     */
    public function input(string $key, mixed  $default = null): mixed
    {
        // Vérifier d'abord dans les données de formulaire
        if ($this->request->has($key)) {
            return $this->request->get($key);
        }

        // Ensuite dans le JSON body
        $json = $this->json();
        if ($json && isset($json[$key])) {
            return $json[$key];
        }

        return $default;
    }

    /**
     * Obtient tous les paramètres d'entrée
     */
    public function all(): array
    {
        return array_merge([$this->query, $this->request, $this->json()] ?? []);
    }

    /**
     * Obtient seulement certains paramètres
     */
    public function only(array $keys): array
    {
        $result = [];
        $all = $this->all();

        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }

        return $result;
    }

    /**
     * Obtient tous les paramètres sauf certains
     */
    public function except(array $keys): array
    {
        $all = $this->all();

        foreach ($keys as $key) {
            unset($all[$key]);
        }

        return $all;
    }

    /**
     * Vérifie si un paramètre existe
     */
    public function has(string $key): bool
    {
        $all = $this->all();
        return isset($all[$key]);
    }

    /**
     * Vérifie si un paramètre existe et n'est pas vide
     */
    public function filled(string $key): bool
    {
        return $this->has($key) && !empty($this->input($key));
    }

    /**
     * Obtient le contenu JSON de la requête
     */
    public function json(): ?array
    {
        if ($this->isJson()) {
            $content = $this->getContent();
            return json_decode($content, true);
        }

        return null;
    }

    /**
     * Vérifie si la requête contient du JSON
     */
    public function isJson(): bool
    {
        return str_contains($this->header('Content-Type', ''), 'application/json');
    }

    /**
     * Obtient un header HTTP
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers->all();
    }

    /**
     * Obtient un paramètre de route
     */
    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Définit les paramètres de route
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Vérifie si la requête est AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Vérifie si la requête est sécurisée (HTTPS)
     */
    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') === 'on' ||
            ($this->server['SERVER_PORT'] ?? '') === '443' ||
            ($this->header('X-Forwarded-Proto') ?? '') === 'https';
    }

    /**
     * Obtient l'adresse IP du client
     */
    public function ip(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if ($this->server->has($key)) {
                $ips = explode(',', $this->server->get($key));
                return trim($ips[0]); // Retourne la première IP
            }
        }

        return $this->server->get('REMOTE_ADDR', '127.0.0.1');
    }

    public function userAgent(): string
    {
        return $this->header('User-Agent', 'unknown');
    }

    /**
     * Remplace les paramètres de la requête
     * @param array $params
     * 
     */
    public function replace(array $params): void
    {
        $this->query->replace($params['query'] ?? []);
        $this->request->replace($params['request'] ?? []);
        $this->server->replace($params['server'] ?? []);
        $this->files->replace($params['files'] ?? []);
        $this->cookies->replace($params['cookies'] ?? []);
        $this->headers->replace($params['headers'] ?? []);
    }



    /**
     * Extrait les headers depuis $_SERVER
     */
    private function getHeadersFromServer(): array
    {
        $headers = $this->headers->all();
        return $headers;
    }
}
