<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

/**
 * Middleware d'authentification basique
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token || !$this->validateToken($token)) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        // Ajouter l'utilisateur à la requête si validé
        // $request->setUser($this->getUserFromToken($token));

        return $next($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $authorization = $request->header('Authorization');

        if ($authorization && str_starts_with($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return null;
    }

    private function validateToken(?string $token): bool
    {
        // TODO: Implémenter la validation du token
        return !empty($token);
    }
}