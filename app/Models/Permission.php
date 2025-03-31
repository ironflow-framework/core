<?php

namespace App\Models;

use IronFlow\Database\Model;

class Permission extends Model
{
   protected $fillable = ['code', 'name', 'description'];

   public function roles()
   {
      return $this->belongsToMany(Role::class, 'role_permissions');
   }

}

