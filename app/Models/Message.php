<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Channel\Traits\HasChannels;
use IronFlow\Database\Iron\Relations\BelongsTo;
use IronFlow\Database\Traits\HasEvents;
use IronFlow\Support\Facades\Channel;

class Message extends Model
{
    use HasChannels;
    use HasEvents;

    protected static string $table = "messages";
    protected array $fillable = [
        'content',
        'user_id',
        'chat_id'
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Message $message) {
            Channel::broadcast('chat.' . $message->chat_id)
                ->withEvent('message.created')
                ->withData($message->load('user'))
                ->send();
        });
    }
}
