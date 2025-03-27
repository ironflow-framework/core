<?php

declare(strict_types=1);

namespace IronFlow\Auth\Middleware;

use IronFlow\Auth\AuthManager;
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

        return $next($request);
    }
}
