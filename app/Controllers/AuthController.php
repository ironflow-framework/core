<?php

declare(strict_types=1);

namespace App\Controllers;

use IronFlow\Http\Controller;

class AuthController extends Controller
{
   public function showLoginForm()
   {
      return $this->view('auth.login');
   }

   public function login()
   {
      // TODO: Implémenter la logique de connexion
      return $this->redirect('/dashboard');
   }

   public function logout()
   {
      // TODO: Implémenter la logique de déconnexion
      return $this->redirect('/login');
   }
}
