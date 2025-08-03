<?php

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $token = $request->headers->get('X-CSRF-TOKEN') ?? $request->input('_token');
            $sessionToken = $_SESSION['_csrf_token'] ?? null;
            if (!$token || $token !== $sessionToken) {
                return new Response('CSRF token mismatch', 419);
            }
        }
        return $next($request);
    }
}
