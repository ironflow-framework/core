<?php

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

/**
 * Interface pour les middleware
 */
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}