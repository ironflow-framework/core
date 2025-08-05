<?php

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

class XssMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $clean = null;
        $clean = function ($value) use (&$clean) {
            if (is_array($value)) {
                return array_map($clean, $value);
            }
            return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        };
        // Nettoie les entrées et remplace dans l'objet Request si possible
        $data = $clean($request->all());
        if (method_exists($request, 'replace')) {
            $request->replace($data);
        } elseif (property_exists($request, 'data')) {
            $request->data = $data;
        }
        return $next($request);
    }
}
