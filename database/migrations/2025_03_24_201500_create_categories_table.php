<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use Ironflow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::createTable('categories', function (Anvil $table) {
         $table->id();
         $table->string('name');
         $table->text('description')->nullable();
         $table->timestamps();
      });
   }

   public function down(): void
   {
      Schema::dropTableIfExists('categories');
   }
};
