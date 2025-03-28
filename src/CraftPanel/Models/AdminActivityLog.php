<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Models;

use IronFlow\Database\Model;

/**
 * Modèle représentant une entrée du journal d'activité dans le CraftPanel
 */
class AdminActivityLog extends Model
{
    /**
     * Nom de la table
     * @var string
     */
    protected string $table = 'admin_activity_log';
    
    /**
     * Attributs assignables en masse
     * @var array
     */
    protected array $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'changes',
    ];
    
    /**
     * Attributs à convertir
     * @var array
     */
    protected array $casts = [
        'changes' => 'array',
    ];
    
    /**
     * Relation avec l'utilisateur
     * @return \IronFlow\Database\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
    
    /**
     * Récupère le modèle associé à cette activité
     * @return \IronFlow\Database\Model|null
     */
    public function getModel(): ?Model
    {
        if (!class_exists($this->model_type)) {
            return null;
        }
        
        return $this->model_type::find($this->model_id);
    }
    
    /**
     * Récupère une description lisible de l'action
     * @return string
     */
    public function getActionDescription(): string
    {
        $modelName = class_basename($this->model_type);
        
        switch ($this->action) {
            case 'create':
                return "Created a new {$modelName}";
            case 'update':
                return "Updated a {$modelName}";
            case 'delete':
                return "Deleted a {$modelName}";
            default:
                return "Performed {$this->action} on a {$modelName}";
        }
    }
}
