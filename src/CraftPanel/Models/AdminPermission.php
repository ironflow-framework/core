<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Models;

use IronFlow\Database\Model;

/**
 * Modu00e8le repru00e9sentant une permission dans le CraftPanel
 */
class AdminPermission extends Model
{
    /**
     * Nom de la table
     * @var string
     */
    protected string $table = 'admin_permissions';
    
    /**
     * Attributs assignables en masse
     * @var array
     */
    protected array $fillable = [
        'name',
        'description',
    ];
    
    /**
     * Relation avec les ru00f4les
     * @return \IronFlow\Database\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permissions', 'permission_id', 'role_id');
    }
}
