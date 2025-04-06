<?php

namespace App\Controllers;

use App\Models\Post;
use IronFlow\Database\Collection;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\Channel;
use IronFlow\Support\Facades\Cache;
use IronFlow\Support\Facades\Storage;
use IronFlow\Forms\Form;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Cache::remember(
            'posts.all',
            function (): Collection {
                return Post::with(['user'])
                    ->where('published', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
            },
            60
        );

        return $this->view('posts.index', [
            'posts' => $posts
        ]);
    }

    public function create(): Response
    {
        $form = Post::form()
            ->method('post')
            ->action(route('posts.store'))
            ->theme('tailwind')
            ->title(trans('Ajouter un post'))
            ->input('title', 'Titre', ['required' => true])
            ->input('content', 'Contenu', ['type' => 'textarea', 'required' => true])
            ->input('image', 'Image', ['type' => 'file', 'accept' => 'image/*'])
            ->input('published', 'Publier', ['type' => 'checkbox'])
            ->button('Enregistrer');

        return $this->view('posts.create', [
            'form' => $form
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $request->validate([
            'title' => 'required|min:3',
            'content' => 'required',
            'image' => 'image|max:2048',
            'published' => 'boolean'
        ]);

        $post = new Post($data);
        $post->user_id = Auth::id();

        if ($request->hasFile('image')) {
            $post->image = Storage::store($request->file('image'), 'posts');
        }

        $post->save();

        Channel::broadcast('posts')
            ->event('post.created')
            ->data($post)
            ->send();

        return $this->redirect('/posts')
            ->with(['success' => 'Article créé avec succès !']);
    }

    public function show(Post $post): Response
    {
        return $this->view('posts/show.twig', [
            'post' => $post->load(['comments.user'])
        ]);
    }

    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        $form = new Form();
        $form->input('title', 'Titre', ['required' => true, 'value' => $post->title]);
        $form->input('content', 'Contenu', ['type' => 'textarea', 'required' => true, 'value' => $post->content]);
        $form->input('image', 'Image', ['type' => 'file', 'accept' => 'image/*']);
        $form->input('published', 'Publier', ['type' => 'checkbox', 'checked' => $post->published]);

        return $this->view('posts/edit.twig', [
            'post' => $post,
            'form' => $form
        ]);
    }

    public function update(Request $request, Post $post): Response
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'title' => 'required|min:3',
            'content' => 'required',
            'image' => 'image|max:2048',
            'published' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = Storage::store($request->file('image'), 'posts');
        }

        $post->update($data);

        Channel::broadcast('posts')
            ->event('post.updated')
            ->data($post)
            ->send();

        return $this->redirect("/posts/{$post->id}")
            ->with(['success' => 'Article mis à jour avec succès !']);
    }

    public function destroy(Post $post): Response
    {
        $this->authorize('delete', $post);

        $post->remove();

        Channel::broadcast('posts')
            ->event('post.deleted')
            ->data(['id' => $post->id])
            ->send();

        return $this->redirect('/posts')
            ->with(['success' => 'Article supprimé avec succès !']);
    }
}
