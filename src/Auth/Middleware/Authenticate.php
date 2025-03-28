<?php

declare(strict_types=1);

namespace IronFlow\Auth\Middleware;

use IronFlow\Auth\AuthManager;
use IronFlow\CraftPanel\Models\AdminUser;
use IronFlow\Http\Middleware;
use IronFlow\Http\Request;
use IronFlow\Http\Response;


class Authenticate extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!AuthManager::getInstance()->check()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            return Response::redirect('/login');
        }

        $user = AuthManager::getInstance()->user();
        if ($user instanceof AdminUser) {
            // Autoriser l'accès
        } else {
            // Rediriger ou refuser l'accès
            return Response::redirect('/unauthorized');
        }

        return $next($request);
    }
}
