<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Comment;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\Channel;

class CommentController
{
    public function store(Request $request, Post $post): Response
    {
        $validated = $request->validate([
            'content' => 'required|min:3'
        ]);

        $comment = new Comment($validated);
        $comment->user_id = Auth::id();
        $comment->post_id = $post->id;
        $comment->save();

        Channel::broadcast("post.{$post->id}")
            ->event('comment.created')
            ->data($comment->load('user'))
            ->send();

        return back()
            ->with('success', 'Commentaire ajouté !');
    }

    public function destroy(Comment $comment): Response
    {
        $this->authorize('delete', $comment);
        $post_id = $comment->post_id;
        
        $comment->delete();

        Channel::broadcast("post.{$post_id}")
            ->event('comment.deleted')
            ->data(['id' => $comment->id])
            ->send();

        return back()
            ->with('success', 'Commentaire supprimé !');
    }
}
