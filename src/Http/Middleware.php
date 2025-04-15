<?php

namespace IronFlow\Http;

use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

abstract class Middleware implements MiddlewareInterface
{
    abstract public function handle(Request $request, callable $next): Response;
}

