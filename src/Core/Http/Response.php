<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http;


/**
 * HTTP Response - Encapsule une réponse HTTP
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{

    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        parent::__construct($content, $statusCode, $headers);
    }

    /**
     * Définit le contenu de la réponse
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Obtient le code de statut HTTP
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Ajoute un header HTTP
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Obtient tous les headers
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * Crée une réponse JSON
     */
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json';
        return new self(json_encode($data), $statusCode, $headers);
    }

    /**
     * Crée une réponse de redirection
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }
}