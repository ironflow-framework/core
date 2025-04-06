<?php

namespace IronFlow\Auth;

use App\Models\User;

abstract class Policy
{
    /**
     * Vérifie si l'utilisateur peut voir la ressource
     */
    public function view(User $user, $model): bool
    {
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut créer la ressource
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut mettre à jour la ressource
     */
    public function update(User $user, $model): bool
    {
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut supprimer la ressource
     */
    public function delete(User $user, $model): bool
    {
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut restaurer la ressource
     */
    public function restore(User $user, $model): bool
    {
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut forcer la suppression de la ressource
     */
    public function forceDelete(User $user, $model): bool
    {
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut effectuer une action quelconque sur la ressource
     */
    public function before(User $user, string $ability, $model = null): ?bool
    {
        // Si l'utilisateur est un administrateur, autoriser toutes les actions
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return null;
    }
}
