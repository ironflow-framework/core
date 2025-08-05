<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;


/**
 * Middleware de logging
 */
class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        // Log de la requête entrante
        $this->logRequest($request);

        $response = $next($request);

        // Log de la réponse
        $executionTime = microtime(true) - $startTime;
        $this->logResponse($request, $response, $executionTime);

        return $response;
    }

    private function logRequest(Request $request): void
    {
        $logData = [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        error_log('REQUEST: ' . json_encode($logData));
    }

    private function logResponse(Request $request, Response $response, float $executionTime): void
    {
        $logData = [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'status_code' => $response->getStatusCode(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        error_log('RESPONSE: ' . json_encode($logData));
    }
}