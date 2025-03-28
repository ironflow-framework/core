<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use Ironflow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::createTable('orders', function (Anvil $table) {
         $table->id();
         $table->integer('user_id');
         $table->decimal('total_amount', 10, 2)->default(0);
         $table->string('status')->default('pending');
         $table->text('shipping_address')->nullable();
         $table->text('billing_address')->nullable();
         $table->string('payment_method')->nullable();
         $table->string('shipping_method')->nullable();
         $table->text('notes')->nullable();
         $table->timestamps();
      });
   }

   public function down(): void
   {
      Schema::dropTableIfExists('orders');
   }
};
