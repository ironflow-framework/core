<?php

namespace App\Policies;

use App\Models\User;
use IronFlow\Auth\Policy;

class PostPolicy extends Policy
{
    public function update(User $user, $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, $post): bool
    {
        return $user->id === $post->user_id;
    }
}
