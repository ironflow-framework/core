<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::createTable('order_products', function (Anvil $table) {
         $table->id();
         $table->integer('order_id');
         $table->integer('product_id');
         $table->integer('quantity')->default(1);
         $table->decimal('price', 10, 2);
         $table->timestamps();

         $table->unique(['order_id', 'product_id']);
      });
   }

   public function down(): void
   {
      Schema::dropTableIfExists('order_products');
   }
};
