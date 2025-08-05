<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

/**
 * Middleware CORS
 */
class CorsMiddleware implements MiddlewareInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['*'],
            'exposed_headers' => [],
            'max_age' => 86400,
            'supports_credentials' => false,
        ], $config);
    }

    public function handle(Request $request, callable $next): Response
    {
        // Gérer les requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response);
    }

    private function handlePreflightRequest(Request $request): Response
    {
        $response = new Response('', 200);
        return $this->addCorsHeaders($request, $response);
    }

    private function addCorsHeaders(Request $request, Response $response): Response
    {
        $origin = $request->header('Origin');

        if ($this->isOriginAllowed($origin)) {
            $response->header('Access-Control-Allow-Origin', $origin ?: '*');
        }

        if ($this->config['supports_credentials']) {
            $response->header('Access-Control-Allow-Credentials', 'true');
        }

        $response->header('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']));
        $response->header('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']));

        if (!empty($this->config['exposed_headers'])) {
            $response->header('Access-Control-Expose-Headers', implode(', ', $this->config['exposed_headers']));
        }

        $response->header('Access-Control-Max-Age', (string) $this->config['max_age']);

        return $response;
    }

    private function isOriginAllowed(?string $origin): bool
    {
        if (in_array('*', $this->config['allowed_origins'])) {
            return true;
        }

        return in_array($origin, $this->config['allowed_origins']);
    }
}