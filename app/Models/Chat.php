<?php

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Channel\Traits\HasChannels;

class Chat extends Model
{
    use HasChannels;

    protected array $fillable = [
        'name',
        'description'
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user');
    }
}
