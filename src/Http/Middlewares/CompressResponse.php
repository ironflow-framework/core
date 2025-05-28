<?php

declare(strict_types=1);

namespace IronFlow\Http\Middlewares;

use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class CompressResponse implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // TODO: Implementer la logique de gestion de ce middleware

        return $next($request);
    }
}
