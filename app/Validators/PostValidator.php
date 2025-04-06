<?php

namespace App\Validators;

use IronFlow\Validation\Validator;

class PostValidator extends Validator
{
    protected array $rules = [
        'title' => 'required|min:3|max:255',
        'content' => 'required',
        'image' => 'nullable|image|max:2048',
        'published' => 'boolean'
    ];

    protected array $messages = [
        'title.required' => 'Le titre est obligatoire',
        'title.min' => 'Le titre doit faire au moins :min caractères',
        'title.max' => 'Le titre ne peut pas dépasser :max caractères',
        'content.required' => 'Le contenu est obligatoire',
        'image.image' => 'Le fichier doit être une image',
        'image.max' => 'L\'image ne peut pas dépasser :max Ko'
    ];
}
