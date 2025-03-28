<?php

namespace IronFlow\CraftPanel\Middleware;

use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\Redirect;
use IronFlow\Support\Facades\Config;

use Closure;

class CraftPanelAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return Redirect::guest(Config::get('auth.login.route'));
        }

        // Vérifier si l'utilisateur a accès au CraftPanel
        if (!Auth::user()->can(Config::get('craftpanel.permissions.view'))) {
            abort(403);
        }

        return $next($request);
    }
}
