<?php

declare(strict_types=1);

namespace IronFlow\Http\Middleware;

use App\Models\User;
use IronFlow\Auth\AuthManager;
use IronFlow\Http\Contracts\MiddlewareInterface;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class Authenticate implements MiddlewareInterface
{
   public function handle(Request $request, callable $next): Response
   {
      if (!AuthManager::getInstance()->check()) {
         if ($request->isJson()) {
            return Response::json([
               'message' => 'Unauthenticated.'
            ], 401);
         }

         return Response::redirect('/login');
      }

      $user = AuthManager::getInstance()->user();
      if ($user instanceof User) {
         // Autoriser l'accès
         session()->flash('welcome', 'Bon retour ' . $user->name . '!');
      } else {
         // Rediriger ou refuser l'accès
         return Response::redirect('/unauthorized');
      }

      return $next($request);
   }
}
