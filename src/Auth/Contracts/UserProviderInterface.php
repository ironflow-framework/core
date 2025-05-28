<?php

/**
 * Interface pour les providers d'utilisateurs
 */
interface UserProviderInterface
{
    public function retrieveById($identifier);
    public function retrieveByToken($identifier, $token);
    public function updateRememberToken($user, $token): void;
    public function retrieveByCredentials(array $credentials);
    public function validateCredentials($user, array $credentials): bool;
}