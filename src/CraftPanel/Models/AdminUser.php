<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Models;

use IronFlow\Database\Model;
use IronFlow\Auth\Authenticatable;
use IronFlow\Auth\Contracts\AuthenticatableInterface;

/**
 * Modèle représentant un administrateur du CraftPanel
 */
class AdminUser extends Model implements AuthenticatableInterface
{
    use Authenticatable;
    
    /**
     * Nom de la table
     * @var string
     */
    protected string $table = 'admin_users';
    
    /**
     * Attributs assignables en masse
     * @var array
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
    ];
    
    /**
     * Attributs cachés
     * @var array
     */
    protected array $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Relation avec les rôles
     * @return \IronFlow\Database\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_user_roles', 'user_id', 'role_id');
    }
    
    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     * @param string $role Nom du rôle
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
    
    /**
     * Vérifie si l'utilisateur a une permission spécifique
     * @param string $permission Nom de la permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }
    
    /**
     * Relation avec les activités
     * @return \IronFlow\Database\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(AdminActivityLog::class, 'user_id');
    }
}
