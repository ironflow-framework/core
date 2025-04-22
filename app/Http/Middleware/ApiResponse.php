<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiResponse
{
   /**
    * Handle an incoming request.
    *
    * @param  \Symfony\Component\HttpFoundation\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
   public function handle(Request $request, Closure $next)
   {
      $response = $next($request);

      if ($response instanceof JsonResponse) {
         $data = json_decode($response->getContent(), true);

         $formattedResponse = [
            'status' => $response->isSuccessful() ? 'success' : 'error',
            'data' => $data,
            'meta' => [
               'timestamp' => time(),
               'version' => '1.0'
            ]
         ];

         $response->setData($formattedResponse);
      }

      return $response;
   }
}
