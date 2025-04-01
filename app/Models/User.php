<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Security\Hasher;

class User extends Model
{
   /**
    * Les attributs qui sont assignables en masse.
    *
    * @var array
    */
   protected array $fillable = [
      'name',
      'email',
      'password',
      'role_id',
      'is_active',
      'two_factor_enabled',
      'two_factor_secret',
      'last_login_at',
      'last_login_ip',
   ];

   /**
    * Les attributs qui doivent être masqués pour la sérialisation.
    *
    * @var array
    */
   protected array $hidden = [
      'password',
      'two_factor_secret',
   ];

   /**
    * Les attributs qui doivent être convertis en types natifs.
    *
    * @var array
    */
   protected array $casts = [
      'email_verified_at' => 'datetime',
      'last_login_at' => 'datetime',
      'is_active' => 'boolean',
      'two_factor_enabled' => 'boolean',
   ];

   /**
    * Le rôle de l'utilisateur
    */
   public function role()
   {
      return $this->belongsTo(Role::class);
   }

   /**
    * Les permissions de l'utilisateur
    */
   public function permissions()
   {
      return $this->role->permissions();
   }

   /**
    * Vérifie si l'utilisateur a une permission spécifique
    *
    * @param  string  $permission
    * @return bool
    */
   public function hasPermission($permission)
   {
      return $this->role->hasPermission($permission);
   }

   /**
    * Vérifie si l'utilisateur a un rôle spécifique
    *
    * @param  string  $role
    * @return bool
    */
   public function hasRole($role)
   {
      return $this->role->name === $role;
   }

   /**
    * Active la 2FA pour l'utilisateur
    *
    * @return void
    */
   public function enable2FA()
   {
      $this->update([
         'two_factor_enabled' => true,
      ]);
   }

   /**
    * Désactive la 2FA pour l'utilisateur
    *
    * @return void
    */
   public function disable2FA()
   {
      $this->update([
         'two_factor_enabled' => false,
         'two_factor_secret' => null,
      ]);
   }

   /**
    * Vérifie un code 2FA
    *
    * @param  string  $code
    * @return bool
    */
   public function verify2FACode($code)
   {
      if (!$this->two_factor_enabled || !$this->two_factor_secret) {
         return false;
      }

      // Implémentation de la vérification 2FA
      // À adapter selon la méthode de 2FA choisie (TOTP, SMS, etc.)
      return true;
   }

   /**
    * Met à jour la date et l'IP de dernière connexion
    *
    * @return void
    */
   public function updateLastLogin()
   {
      $this->update([
         'last_login_at' => now(),
         'last_login_ip' => request()->ip(),
      ]);
   }

   /**
    * Vérifie si le mot de passe est valide
    *
    * @param  string  $password
    * @return bool
    */
   public function validatePassword($password)
   {
      return Hasher::verify($password, $this->password);
   }

   /**
    * Change le mot de passe de l'utilisateur
    *
    * @param  string  $password
    * @return void
    */
   public function changePassword($password)
   {
      $this->update([
         'password' => Hasher::hash($password),
      ]);
   }

   /**
    * Vérifie si l'utilisateur est le même que l'utilisateur connecté
    *
    * @return bool
    */
   public function isCurrentUser()
   {
      return $this->id === Auth::id();
   }
}
