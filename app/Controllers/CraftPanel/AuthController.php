<?php

namespace App\Controllers\CraftPanel;

use IronFlow\Http\Controller;    
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;


class AuthController extends Controller
{
   
   public function showLoginForm(): Response
   {
      if (Auth::guard('craftpanel')->check()) {
         return $this->redirect('craftpanel.dashboard');
      }

      return $this->view('craftpanel.auth.login');
   }

   public function login(Request $request): Response
   {
      $credentials = $request->only(['email', 'password']);
      $remember = $request->boolean('remember', false);

      if (Auth::guard('craftpanel')->attempt($credentials, $remember)) {
         $user = Auth::guard('craftpanel')->user();

         // Vérification de l'état du compte
         if (!$user->is_active) {
            Auth::guard('craftpanel')->logout();
            return Redirect::back()
               ->withInput()
               ->withErrors(['email' => 'Votre compte est désactivé.']);
         }

         // Vérification de la 2FA si activée
         if (config('craftpanel.security.require_2fa') && !$user->two_factor_enabled) {
            return Redirect::route('craftpanel.2fa.setup');
         }

         Session::regenerate();
         return Redirect::intended(route('craftpanel.dashboard'));
      }

      return Redirect::back()
         ->withInput()
         ->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.']);
   }

   /**
    * Déconnecte l'utilisateur
    *
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function logout()
   {
      Auth::guard('craftpanel')->logout();
      Session::flush();
      return Redirect::route('craftpanel.login');
   }

   /**
    * Affiche le formulaire de configuration 2FA
    *
    * @return \IronFlow\Support\Facades\View
    */
   public function show2FASetup()
   {
      $user = Auth::guard('craftpanel')->user();
      return View::make('craftpanel.auth.2fa-setup', compact('user'));
   }

   /**
    * Configure la 2FA pour l'utilisateur
    *
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function setup2FA()
   {
      $user = Auth::guard('craftpanel')->user();
      $code = Request::input('code');

      if ($user->verify2FACode($code)) {
         $user->enable2FA();
         return Redirect::route('craftpanel.dashboard')
            ->with('success', 'La 2FA a été activée avec succès.');
      }

      return Redirect::back()
         ->withErrors(['code' => 'Le code 2FA est invalide.']);
   }
}
