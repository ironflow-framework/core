<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use Ironflow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createTable('products', function (Anvil $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->foreignId('category');
            $table->foreign('category_id', 'Categories');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropTableIfExists('products');
    }
};
