<?php

declare(strict_types=1);

namespace App\Controllers\CraftPanel;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Http\Controller;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Session;
use IronFlow\Support\Facades\Redirect;
use IronFlow\Support\Facades\Notification;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): Response
    {
        if (Auth::guard('craftpanel')->check()) {
            return $this->redirect('craftpanel.dashboard');
        }

        return $this->view('craftpanel.auth.login');
    }

    /**
     * Gère la tentative de connexion
     */
    public function login(Request $request): Response
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember', false);

        if (Auth::guard('craftpanel')->attempt($credentials, $remember)) {
            $user = Auth::guard('craftpanel')->user();

            // Vérification de l'état du compte
            if (!$user->is_active) {
                Auth::guard('craftpanel')->logout();
                Notification::error('Votre compte est désactivé.');
                return $this->redirectBack()->withInput();
            }

            // Vérification de la 2FA si activée
            if (config('craftpanel.security.require_2fa') && !$user->two_factor_enabled) {
                Notification::warning('La double authentification est requise pour votre compte.');
                return $this->redirectToRoute('craftpanel.2fa.setup');
            }

            Session::regenerate();
            Notification::success('Connexion réussie ! Bienvenue dans le CraftPanel.');
            return $this->redirectIntended(route('craftpanel.dashboard'));
        }

        Notification::error('Ces identifiants ne correspondent pas à nos enregistrements.');
        return $this->redirectBack()->withInput();
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): Response
    {
        Auth::guard('craftpanel')->logout();
        Session::invalidate();
        
        Notification::info('Vous avez été déconnecté avec succès.');
        return $this->redirectToRoute('craftpanel.login');
    }

    /**
     * Affiche le formulaire de configuration 2FA
     */
    public function show2FASetup(): Response
    {
        $user = Auth::guard('craftpanel')->user();
        return View::make('craftpanel.auth.2fa-setup', compact('user'));
    }

    /**
     * Configure la 2FA pour l'utilisateur
     */
    public function setup2FA(Request $request): Response
    {
        $user = Auth::guard('craftpanel')->user();
        $code = $request->input('code');

        if ($user->verify2FACode($code)) {
            $user->enable2FA();
            return Redirect::route('craftpanel.dashboard')
                ->with(['success' => 'La 2FA a été activée avec succès.']);
        }

        return Redirect::back()
            ->withErrors(['code' => 'Le code 2FA est invalide.']);
    }
}
