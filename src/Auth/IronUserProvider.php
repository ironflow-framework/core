<?php

namespace IronFlow\Auth;

use IronFlow\Auth\Contracts\UserProviderInterface;
use IronFlow\Database\Model;

/**
 * Fournisseur d'utilisateurs utilisant Iron ORM
 * Inspiré de Laravel EloquentUserProvider
 */
class IronUserProvider implements UserProviderInterface
{
    protected $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Récupérer un utilisateur par son identifiant
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    /**
     * Récupérer un utilisateur par son token "remember me"
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        $user = $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        if ($user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)) {
            return $user;
        }

        return null;
    }

    /**
     * Mettre à jour le token "remember me"
     */
    public function updateRememberToken($user, $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Récupérer un utilisateur par ses credentials
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (
            empty($credentials) ||
            (count($credentials) === 1 && array_key_exists('password', $credentials))
        ) {
            return null;
        }

        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if ($key !== 'password') {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Valider les credentials d'un utilisateur
     */
    public function validateCredentials($user, array $credentials): bool
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        return $this->hasher()->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Créer une nouvelle instance du modèle
     */
    public function createModel()
    {
        $class = '\\' . ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Obtenir le hasher
     */
    protected function hasher(): PasswordHasher
    {
        return new PasswordHasher();
    }
}
