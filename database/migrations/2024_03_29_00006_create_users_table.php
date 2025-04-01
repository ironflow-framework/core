<?php

use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Migrations\Migration;

return new class extends Migration
{

   public function up(): void
   {
      Schema::createTable('craft_users', function (Anvil $table) {
         $table->id();
         $table->string('name');
         $table->string('email')->unique();
         $table->string('password');
         $table->foreignId('role_id')->constrained(['role', 'id'])->onDelete('cascade');
         $table->boolean('is_active')->default(true);
         $table->boolean('two_factor_enabled')->default(false);
         $table->string('two_factor_secret')->nullable();
         $table->timestamp('email_verified_at')->nullable();
         $table->timestamp('last_login_at')->nullable();
         $table->string('last_login_ip')->nullable();
         $table->rememberToken();
         $table->timestamps();
         $table->softDeletes();
      });
   }


   public function down(): void
   {
      Schema::dropTableIfExists('craft_users');
   }
};
