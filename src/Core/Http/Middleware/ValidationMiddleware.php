<?php

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

class ValidationMiddleware
{
    protected array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function handle(Request $request, callable $next): Response
    {
        foreach ($this->rules as $field => $rule) {
            $value = $request->input($field);
            if ($rule === 'required' && (is_null($value) || $value === '')) {
                return new Response("Le champ $field est requis", 422);
            }
            // Ajoute d'autres règles ici (ex: email, min, max...)
        }
        return $next($request);
    }
}
