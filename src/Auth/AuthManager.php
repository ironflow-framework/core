<?php

declare(strict_types=1);

namespace IronFlow\Auth;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Session\Session;
use IronFlow\Support\Config;
use IronFlow\Database\Model;
use App\Models\User;

/**
 * Gestionnaire d'authentification principal
 * Inspiré de Laravel Guard et Django Auth
 */
class AuthManager
{
    protected $session;
    protected $config;
    protected $user = null;
    protected $userProvider;
    protected $loginAttempts = [];

    public function __construct(Session $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;
        $this->userProvider = new IronUserProvider(
            $config->get('auth.providers.users.model', User::class)
        );
    }

    /**
     * Tentative de connexion d'un utilisateur
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->userProvider->retrieveByCredentials($credentials);

        if ($user && $this->userProvider->validateCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }

        $this->recordLoginAttempt($credentials);
        return false;
    }

    /**
     * Connecter un utilisateur
     */
    public function login($user, bool $remember = false): void
    {
        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {
            $this->queueRecallerCookie($user);
        }

        $this->setUser($user);
    }

    /**
     * Déconnecter l'utilisateur actuel
     */
    public function logout(): void
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if ($user) {
            $this->cycleRememberToken($user);
        }

        $this->user = null;
    }

    /**
     * Obtenir l'utilisateur actuellement authentifié
     */
    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        if ($id !== null) {
            $this->user = $this->userProvider->retrieveById($id);
        }

        // Vérifier le cookie "remember me"
        if ($this->user === null) {
            $this->user = $this->getUserByRecaller();
        }

        return $this->user;
    }

    /**
     * Vérifier si un utilisateur est connecté
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Vérifier si aucun utilisateur n'est connecté
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Obtenir l'ID de l'utilisateur connecté
     */
    public function id()
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    /**
     * Valider les informations d'identification d'un utilisateur
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->userProvider->retrieveByCredentials($credentials);

        return $user && $this->userProvider->validateCredentials($user, $credentials);
    }

    /**
     * Enregistrer une tentative de connexion
     */
    protected function recordLoginAttempt(array $credentials): void
    {
        $key = $this->throttleKey($credentials);
        $attempts = $this->session->get("login_attempts.{$key}", 0) + 1;

        $this->session->put("login_attempts.{$key}", $attempts);
        $this->session->put("login_attempts.{$key}.time", time());
    }

    /**
     * Vérifier si trop de tentatives de connexion
     */
    public function hasTooManyLoginAttempts(array $credentials): bool
    {
        $key = $this->throttleKey($credentials);
        $attempts = $this->session->get("login_attempts.{$key}", 0);
        $lastAttempt = $this->session->get("login_attempts.{$key}.time", 0);

        $maxAttempts = $this->config->get('auth.security.max_login_attempts', 5);
        $lockoutTime = $this->config->get('auth.security.lockout_duration', 900);

        if ($attempts >= $maxAttempts) {
            return (time() - $lastAttempt) < $lockoutTime;
        }

        return false;
    }

    /**
     * Réinitialiser les tentatives de connexion
     */
    public function clearLoginAttempts(array $credentials): void
    {
        $key = $this->throttleKey($credentials);
        $this->session->forget("login_attempts.{$key}");
        $this->session->forget("login_attempts.{$key}.time");
    }

    /**
     * Générer une clé de throttling pour les tentatives
     */
    protected function throttleKey(array $credentials): string
    {
        $email = $credentials['email'] ?? 'unknown';
        $ip = request()->ip() ?? 'unknown';

        return strtolower($email) . '|' . $ip;
    }

    /**
     * Mettre à jour la session avec l'ID utilisateur
     */
    protected function updateSession($id): void
    {
        $this->session->put($this->getName(), $id);
        $this->session->migrate(true);
    }

    /**
     * Obtenir le nom de la clé de session
     */
    protected function getName(): string
    {
        return $this->config->get('auth.session.key', 'auth_user_id');
    }

    /**
     * Définir l'utilisateur actuel
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * Effacer les données utilisateur du stockage
     */
    protected function clearUserDataFromStorage(): void
    {
        $this->session->forget($this->getName());
    }

    /**
     * Ajouter le cookie "remember me"
     */
    protected function queueRecallerCookie($user): void
    {
        $token = $this->generateRecallerToken();

        // Sauvegarder le token dans la base de données
        $user->setRememberToken($token);
        $user->save();

        $cookieName = $this->config->get('auth.session.cookie_name', 'ironflow_remember');
        $cookieExpire = $this->config->get('auth.session.cookie_expire', 43200); // 30 jours

        response()->cookie($cookieName, $user->getAuthIdentifier() . '|' . $token, $cookieExpire);
    }

    /**
     * Obtenir l'utilisateur via le cookie "remember me"
     */
    protected function getUserByRecaller()
    {
        $cookieName = $this->config->get('auth.session.cookie_name', 'ironflow_remember');
        $recaller = request()->cookie($cookieName);

        if ($recaller && str_contains($recaller, '|')) {
            [$id, $token] = explode('|', $recaller, 2);

            $user = $this->userProvider->retrieveByToken($id, $token);

            if ($user) {
                $this->updateSession($user->getAuthIdentifier());
                return $user;
            }
        }

        return null;
    }

    /**
     * Générer un token "remember me"
     */
    protected function generateRecallerToken(): string
    {
        return hash('sha256', random_bytes(40));
    }

    /**
     * Renouveler le token "remember me"
     */
    protected function cycleRememberToken($user): void
    {
        if (method_exists($user, 'setRememberToken')) {
            $user->setRememberToken($this->generateRecallerToken());
            $user->save();
        }
    }
}
