<?php

declare(strict_types=1);

namespace App\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class AuthController extends Controller
{
   /**
    * Affiche le formulaire de connexion
    * 
    * @return Response
    */
   public function showLoginForm(): Response
   {
      return $this->view('auth.login');
   }

   /**
    * Traite la demande de connexion
    * 
    * @param Request $request
    * @return Response
    */
   public function login(Request $request): Response
   {
      // Logique d'authentification simplifiée
      $email = $request->input('email');
      $password = $request->input('password');

      // TODO: Implémenter la vérification d'authentification

      return $this->redirect('/dashboard');
   }

   /**
    * Déconnecte l'utilisateur
    * 
    * @return Response
    */
   public function logout(): Response
   {
      // TODO: Déconnecter l'utilisateur
      return $this->redirect('/login');
   }

   /**
    * Affiche le formulaire pour demander un lien de réinitialisation de mot de passe
    * 
    * @return Response
    */
   public function showForgotPasswordForm(): Response
   {
      return $this->view('auth.forgot-password');
   }

   /**
    * Envoie un email avec un lien de réinitialisation de mot de passe
    * 
    * @param Request $request
    * @return Response
    */
   public function sendResetLinkEmail(Request $request): Response
   {
      // TODO: Implémenter l'envoi d'email
      return $this->back();
   }

   /**
    * Affiche le formulaire de réinitialisation de mot de passe
    * 
    * @param string $token
    * @return Response
    */
   public function showResetPasswordForm(string $token): Response
   {
      return $this->view('auth.reset-password', [
         'token' => $token
      ]);
   }

   /**
    * Réinitialise le mot de passe
    * 
    * @param Request $request
    * @return Response
    */
   public function resetPassword(Request $request): Response
   {
      // TODO: Implémenter la logique de réinitialisation
      return $this->redirect('/login');
   }
}
