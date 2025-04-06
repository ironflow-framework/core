<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\BelongsTo;

class Comment extends Model
{
    protected static string $table = "comments";
    protected array $fillable = [
        'content',
        'post_id',
        'user_id'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
