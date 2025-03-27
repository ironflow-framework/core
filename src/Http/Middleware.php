<?php

namespace IronFlow\Http;

abstract class Middleware
{
   abstract  public function handle(Request $request, callable $next): Response;

   public function next(Request $request, callable $next): Response
   {
      return $next($request);
   }
}