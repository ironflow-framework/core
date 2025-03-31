<?php

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

      // Création des rôles par défaut
      $db = DB::getInstance()->getConnection();
      $db->insert('roles', [
         [
            'name' => 'admin',
            'description' => 'Administrateur avec tous les droits',
            'created_at' => now(),
            'updated_at' => now(),
         ],
         [
            'name' => 'user',
            'description' => 'Utilisateur standard',
            'created_at' => now(),
            'updated_at' => now(),
         ],
      ]);
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
