<?php

namespace IronFlow\CraftPanel\Middleware;

use IronFlow\Http\Middleware;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;

class AuthenticateAdmin extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if ($request->isAjax()) {
                return Response::json(['error' => 'Unauthorized'], 401);
            }

            session()->set('error', 'Vous devez être administrateur pour accéder au CraftPanel.');
            return redirect('/login');
        }

        return $next($request);
    }
}
