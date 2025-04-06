<?php

namespace App\Models;

use IronFlow\Database\Iron\Relations\BelongsTo;
use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\HasMany;
use IronFlow\Database\Traits\HasForm;

class Post extends Model
{
    use HasForm;
    protected static string $table = "posts";
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
