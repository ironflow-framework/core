<?php

namespace App\Validation;

use IronFlow\Validation\Validator;

class CommentValidator extends Validator
{
    protected array $rules = [
        'content' => 'required|min:3|max:1000'
    ];

    protected array $messages = [
        'content.required' => 'Le commentaire est obligatoire',
        'content.min' => 'Le commentaire doit faire au moins :min caractères',
        'content.max' => 'Le commentaire ne peut pas dépasser :max caractères'
    ];
}
