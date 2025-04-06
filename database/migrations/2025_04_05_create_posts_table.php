<?php

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Schema\Anvil;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createTable('posts', function (Anvil $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained();
            $table->string('image')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropTableIfExists('posts');
    }
};

