<?php

namespace Database\Migrations;

use Carbon\Carbon;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Migrations\Migration;
use IronFlow\Support\Facades\DB;
return new class extends Migration
{
   public function up(): void
   {
      Schema::createTable('permissions', function (Anvil $table) {
         $table->id();
         $table->string('name')->unique();
         $table->string('description')->nullable();
         $table->string('module');
         $table->timestamps();
      });

      Schema::createTable('role_permissions', function (Anvil $table) {
         $table->id();
         $table->foreignId('role_id')->constrained(['roles', 'id'])->onDelete('cascade');
         $table->foreign('permission_id', 'permissions', 'id')->onDelete('cascade');
         $table->timestamps();
      });

      // Création des permissions par défaut
      $permissions = [
         // Permissions système
         [
            'name' => 'system.view',
            'description' => 'Voir les informations système',
            'module' => 'system',
         ],
         [
            'name' => 'system.manage',
            'description' => 'Gérer les paramètres système',
            'module' => 'system',
         ],

         // Permissions d'administration
         [
            'name' => 'admin.view',
            'description' => 'Voir le panneau d\'administration',
            'module' => 'admin',
         ],
         [
            'name' => 'admin.manage_users',
            'description' => 'Gérer les utilisateurs',
            'module' => 'admin',
         ],
         [
            'name' => 'admin.manage_roles',
            'description' => 'Gérer les rôles',
            'module' => 'admin',
         ],
         [
            'name' => 'admin.manage_permissions',
            'description' => 'Gérer les permissions',
            'module' => 'admin',
         ],
         [
            'name' => 'admin.manage_settings',
            'description' => 'Gérer les paramètres',
            'module' => 'admin',
         ],

         // Permissions utilisateur
         [
            'name' => 'user.view',
            'description' => 'Voir son profil',
            'module' => 'user',
         ],
         [
            'name' => 'user.edit',
            'description' => 'Modifier son profil',
            'module' => 'user',
         ],
         [
            'name' => 'user.change_password',
            'description' => 'Changer son mot de passe',
            'module' => 'user',
         ],
         [
            'name' => 'user.manage_2fa',
            'description' => 'Gérer la 2FA',
            'module' => 'user',
         ],
      ];

      $db = DB::getInstance()->getConnection();

      foreach ($permissions as $permission) {
         $db->insert('permissions', [
            'name' => $permission['name'],
            'description' => $permission['description'],
            'module' => $permission['module'],
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }

      // Attribution des permissions au rôle admin
      $adminRole = DB::table('roles')->where('name', 'admin')->first();
      $allPermissions = DB::table('permissions')->pluck('id');

      foreach ($allPermissions as $permissionId) {
         DB::table('role_permissions')->insert([
            'role_id' => $adminRole->id,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }

      // Attribution des permissions de base au rôle user
      $userRole = DB::table('roles')->where('name', 'user')->first();
      $userPermissions = DB::table('permissions')
         ->whereIn('name', ['user.view', 'user.edit', 'user.change_password'])
         ->pluck('id');

      foreach ($userPermissions as $permissionId) {
         DB::table('role_permissions')->insert([
            'role_id' => $userRole->id,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }
   }


   public function down():void
   {
      Schema::dropTableIfExists('role_permissions');
      Schema::dropTableIfExists('permissions');
   }
};
