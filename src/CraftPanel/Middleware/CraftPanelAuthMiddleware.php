<?php

namespace IronFlow\CraftPanel\Middleware;

use IronFlow\Http\Middleware;
use IronFlow\Http\Response;
use IronFlow\Http\Request;
use IronFlow\Support\Facades\Config;

class CraftPanelAuthMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \IronFlow\Http\Request  $request
     * @param  callable  $next
     * @return \IronFlow\Http\Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return Response::redirect(Config::get('auth.login.route', 'login'));
        }

        // Vérifier si l'utilisateur a accès au CraftPanel
        $permission = Config::get('craftpanel.permissions.view', 'craftpanel.view');
        if (!auth()->user()->can($permission)) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
