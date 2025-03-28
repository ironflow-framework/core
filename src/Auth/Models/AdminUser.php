<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Models;

use IronFlow\Database\Model;
use IronFlow\Auth\Traits\Authenticatable;
use IronFlow\Support\Hasher;
use IronFlow\Support\Utils\Str;
use Carbon\Carbon;

/**
 * Modèle représentant un administrateur du CraftPanel
 */
class AdminUser extends Model
{
   use Authenticatable;

   /**
    * Transforme un User en AdminUser
    * @param App\Models\User $user
    * @return AdminUser
    */
   public static function fromUser(App\Models\User $user): self
   {
      $adminUser = new self();
      $adminUser->name = $user->name;
      $adminUser->email = $user->email;
      $adminUser->password = Hasher::hash($user->password);
      $adminUser->remember_token = Str::random(10);
      $adminUser->provider = $user->provider;
      $adminUser->provider_id = $user->provider_id;
      $adminUser->avatar = $user->avatar;
      $adminUser->role = 'admin';
      $adminUser->status = 'active';
      $adminUser->created_at = Carbon::now();
      $adminUser->updated_at = Carbon::now();
      $adminUser->save();
      return $adminUser;
   }
}
