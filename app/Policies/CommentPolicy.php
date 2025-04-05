<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use IronFlow\Auth\Policy;

class CommentPolicy extends Policy
{
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
