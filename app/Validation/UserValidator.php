<?php

namespace App\Validation;

use IronFlow\Validation\Validator;

class UserValidator extends Validator
{
   public function rules(): array
   {
      return [
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users',
         'password' => 'required|string|min:' . config('craftpanel.security.password_min_length'),
         'role' => 'required|exists:roles,id',
      ];
   }

   public function messages(): array
   {
      return [
         'name.required' => 'Le nom est requis.',
         'email.required' => 'L\'email est requis.',
         'email.email' => 'L\'email doit être une adresse email valide.',
         'email.unique' => 'L\'email doit être unique.',
         'password.required' => 'Le mot de passe est requis.',
         'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
         'role.required' => 'Le rôle est requis.',
         'role.exists' => 'Le rôle doit être valide.',
      ];
   }
}
