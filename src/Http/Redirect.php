<?php

namespace IronFlow\Http;

use IronFlow\Http\Response;

class Redirect
{

   public static function guest(string $path): Response
   {
      return Response::redirect($path, 302);
   }

   public static function to(string $path): Response
   {
      return Response::redirect($path, 302);
   }

   public static function back(): Response
   {
      return Response::redirect($_SERVER['HTTP_REFERER'], 302);
   }

   public static function route(string $name, array $parameters = []): Response
   {
      return Response::redirect(route($name, $parameters), 302);
   }
}
