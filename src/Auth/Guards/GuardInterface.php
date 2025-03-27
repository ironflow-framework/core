<?php

declare(strict_types=1);

namespace IronFlow\Auth\Guards;

use IronFlow\Database\Model;

interface GuardInterface
{
    /**
     * Tente d'authentifier l'utilisateur avec les identifiants fournis
     */
    public function attempt(array $credentials): bool;

    /**
     * Connecte un utilisateur
     */
    public function login(Model $user): void;

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void;

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public function check(): bool;

    /**
     * Récupère l'utilisateur authentifié
     */
    public function user(): ?Model;

    /**
     * Récupère l'ID de l'utilisateur authentifié
     */
    public function id(): ?int;

    /**
     * Valide les identifiants sans authentifier l'utilisateur
     */
    public function validate(array $credentials): bool;
}
