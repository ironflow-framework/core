<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Models;

use IronFlow\Database\Model;

/**
 * Modèle représentant un rôle d'administrateur dans le CraftPanel
 */
class AdminRole extends Model
{
    /**
     * Nom de la table
     * @var string
     */
    protected string $table = 'admin_roles';
    
    /**
     * Attributs assignables en masse
     * @var array
     */
    protected array $fillable = [
        'name',
        'description',
    ];
    
    /**
     * Relation avec les utilisateurs
     * @return \IronFlow\Database\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'admin_user_roles', 'role_id', 'user_id');
    }
    
    /**
     * Relation avec les permissions
     * @return \IronFlow\Database\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permissions', 'role_id', 'permission_id');
    }
    
    /**
     * Attribue des permissions au rôle
     * @param array $permissions Permissions à attribuer
     * @return void
     */
    public function givePermissionsTo(array $permissions): void
    {
        $this->permissions()->attach($permissions);
    }
    
    /**
     * Retire des permissions au rôle
     * @param array $permissions Permissions à retirer
     * @return void
     */
    public function revokePermissionsTo(array $permissions): void
    {
        $this->permissions()->detach($permissions);
    }
    
    /**
     * Synchronise les permissions du rôle
     * @param array $permissions Permissions à synchroniser
     * @return void
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions()->sync($permissions);
    }
}
