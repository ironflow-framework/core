<?php

use App\Models\Role;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Migrations\Migration;
use IronFlow\Support\Facades\DB;

return new class extends Migration
{
   /**
    * Exécute la migration.
    *
    * @return void
    */
   public function up(): void
   {
      Schema::createTable('roles', function (Anvil $table) {
         $table->id();
         $table->string('name')->unique();
         $table->string('description')->nullable();
         $table->timestamps();
      });

      $roles = [
         [
            'name' => 'admin',
            'description' => 'Administrateur avec tous les droits',
         ],
         [
            'name' => 'user',
            'description' => 'Utilisateur standard',
         ],
      ];

      $db = DB::getInstance();

      foreach ($roles as $role) {
         $db->beginTransaction();
         $db->insert('roles', [
            'name' => $role['name'],
            'description' => $role['description'],
            'created_at' => now(),
            'updated_at' => now(),
         ]);
         $db->commit();
      }
   }

   /**
    * Annule la migration.
    *
    * @return void
    */
   public function down(): void
   {
      Schema::dropTableIfExists('roles');
   }
};
