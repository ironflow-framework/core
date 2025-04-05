<?php

use IronFlow\Database\Migration;
use IronFlow\Database\Schema;
use IronFlow\Database\Table;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Table $table) {
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
        Schema::dropIfExists('posts');
    }
}
