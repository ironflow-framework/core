<?php

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createTable('comments', function (Anvil $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropTableIfExists('comments');
    }
};
