<?php

namespace IronFlow\Http;

use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

/**
 * Classe pour les Middlewares
 * 
 * Cette classe represente la classe de base pour les middlewares
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * Methode handle
     * @param \IronFlow\Http\Request $request
     * @param callable $next
     * @return \IronFlow\Http\Response
     */
    abstract public function handle(Request $request, callable $next): Response;
}

