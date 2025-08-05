<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

/**
 * Middleware de rate limiting
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxAttempts;
    private int $decayMinutes;
    private array $cache = []; // En production, utiliser Redis ou autre

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key)) {
            return Response::json([
                'error' => 'Too Many Requests',
                'retry_after' => $this->getTimeUntilNextRetry($key)
            ], 429);
        }

        $this->incrementAttempts($key);

        $response = $next($request);

        return $this->addHeaders($response, $key);
    }

    private function resolveRequestSignature(Request $request): string
    {
        return sha1($request->ip() . '|' . $request->getUri());
    }

    private function tooManyAttempts(string $key): bool
    {
        return $this->getAttempts($key) >= $this->maxAttempts;
    }

    private function getAttempts(string $key): int
    {
        return $this->cache[$key]['attempts'] ?? 0;
    }

    private function incrementAttempts(string $key): void
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = [
                'attempts' => 0,
                'reset_time' => time() + ($this->decayMinutes * 60)
            ];
        }

        // Reset si le temps est écoulé
        if (time() > $this->cache[$key]['reset_time']) {
            $this->cache[$key] = [
                'attempts' => 0,
                'reset_time' => time() + ($this->decayMinutes * 60)
            ];
        }

        $this->cache[$key]['attempts']++;
    }

    private function getTimeUntilNextRetry(string $key): int
    {
        return max(0, $this->cache[$key]['reset_time'] - time());
    }

    private function addHeaders(Response $response, string $key): Response
    {
        $attempts = $this->getAttempts($key);
        $remaining = max(0, $this->maxAttempts - $attempts);

        $response->header('X-RateLimit-Limit', (string) $this->maxAttempts);
        $response->header('X-RateLimit-Remaining', (string) $remaining);

        if ($remaining === 0) {
            $response->header('X-RateLimit-Reset', (string) $this->cache[$key]['reset_time']);
        }

        return $response;
    }
}