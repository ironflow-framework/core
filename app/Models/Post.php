<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Relations\HasMany;
use IronFlow\Database\Relations\BelongsTo;

class Post extends Model
{
    protected array $fillable = [
        'title',
        'content',
        'user_id',
        'image',
        'published'
    ];

    protected array $casts = [
        'published' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
