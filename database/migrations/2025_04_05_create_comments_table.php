<?php

use IronFlow\Database\Migration;
use IronFlow\Database\Schema;
use IronFlow\Database\Table;

class CreateCommentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Table $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
}
