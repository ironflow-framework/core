<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Channel\Traits\HasChannels;

class Message extends Model
{
    use HasChannels;

    protected array $fillable = [
        'content',
        'user_id',
        'chat_id'
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            $message->broadcast('chat.' . $message->chat_id)
                ->event('message.created')
                ->data($message->load('user'))
                ->send();
        });
    }
}
