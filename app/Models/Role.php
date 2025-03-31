<?php

namespace App\Models;

use IronFlow\Database\Model;

class Role extends Model
{

   protected $fillable = [
      'name',
      'description',
   ];

   /**
    * Les utilisateurs qui ont ce rôle
    */
   public function users()
   {
      return $this->hasMany(User::class);
   }

   /**
    * Les permissions associées à ce rôle
    */
   public function permissions()
   {
      return $this->belongsToMany(Permission::class, 'role_permissions');
   }

   /**
    * Vérifie si le rôle a une permission spécifique
    *
    * @param  string  $permission
    * @return bool
    */
   public function hasPermission($permission)
   {
      return $this->permissions()->where('name', $permission)->exists();
   }

   /**
    * Ajoute une permission au rôle
    *
    * @param  string|array  $permissions
    * @return void
    */
   public function givePermission($permissions)
   {
      $permissions = (array) $permissions;
      $this->permissions()->attach($permissions);
   }

   /**
    * Retire une permission du rôle
    *
    * @param  string|array  $permissions
    * @return void
    */
   public function revokePermission($permissions)
   {
      $permissions = (array) $permissions;
      $this->permissions()->detach($permissions);
   }

   /**
    * Synchronise les permissions du rôle
    *
    * @param  array  $permissions
    * @return void
    */
   public function syncPermissions($permissions)
   {
      $this->permissions()->sync($permissions);
   }

   /**
    * Vérifie si le rôle est utilisé par des utilisateurs
    *
    * @return bool
    */
   public function hasUsers()
   {
      return $this->users()->count() > 0;
   }

   /**
    * Vérifie si le rôle est le rôle par défaut
    *
    * @return bool
    */
   public function isDefault()
   {
      return $this->name === 'user';
   }

   /**
    * Vérifie si le rôle est le rôle administrateur
    *
    * @return bool
    */
   public function isAdmin()
   {
      return $this->name === 'admin';
   }
}
