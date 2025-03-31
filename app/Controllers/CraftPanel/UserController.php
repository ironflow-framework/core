<?php

namespace App\Controllers\CraftPanel;

use App\Models\Role;
use App\Models\User;
use App\Validation\UserValidator;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Security\Hasher;
use IronFlow\Validation\Validator;

class UserController extends Controller
{
   public function index(Request $request): Response
   {
      $users = User::paginate(10);
      return $this->view('craftpanel.users.index', compact('users'));
   }

   public function create(Request $request): Response
   {
      $roles = Role::all();
      $old = $request->old();
      return $this->view('craftpanel.users.create', compact('roles', 'old'));
   }

   public function store(Request $request): Response
   {
      $validator = UserValidator::make($request->all());

      if (!$validator->fails()) {
         return $this->redirect()->back()
            ->withErrors($validator->errors())
            ->withInput();
      }

      $user = User::create([
         'name' => $request->input('name'),
         'email' => $request->input('email'),
         'password' => Hasher::hash($request->input('password')),
         'role_id' => $request->input('role'),
         'is_active' => true,
      ]);

      return $this->route('craftpanel.users.index')
         ->with('success', 'L\'utilisateur a été créé avec succès.');
   }

   public function edit(Request $request, $id): Response
   {
      $user = User::findOrFail($id);
      $roles = Role::all();
      $old = $request->old();
      return $this->view('craftpanel.users.edit', compact('user', 'roles', 'old'));
   }

   public function update(Request $request, $id): Response
   {
      $user = User::findOrFail($id);

      $validator = Validator::make($request->all(), [
         'name' => 'required|string|max:255',
         'email' => 'required|string|email|max:255|unique:users,email,' . $id,
         'role' => 'required|exists:roles,id',
      ]);

      if ($validator->validate()) {
         return $this->back()
            ->withErrors($validator->errors())
            ->withInput();
      }

      $user->update([
         'name' => $request->input('name'),
         'email' => $request->input('email'),
         'role_id' => $request->input('role'),
      ]);

      if ($request->filled('password')) {
         $passwordValidator = Validator::make($request->input('password'), [
            'password' => 'required|string|min:' . config('craftpanel.security.password_min_length'),
         ]);

         if ($passwordValidator->validate()) {
            return $this->back()
               ->withErrors($passwordValidator->errors())
               ->withInput();
         }

         $user->update([
            'password' => Hasher::hash($request->input('password')),
         ]);
      }

      return $this->route('craftpanel.users.index')
         ->with('success', 'L\'utilisateur a été mis à jour avec succès.');
   }

   /**
    * Supprime un utilisateur
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function destroy($id)
   {
      $user = User::findOrFail($id);

      // Empêcher la suppression de l'utilisateur connecté
      if ($user->id === auth()->id()) {
         return $this->back()
            ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
      }

      $user->delete($id);

      return $this->route('craftpanel.users.index')
         ->with('success', 'L\'utilisateur a été supprimé avec succès.');
   }

   /**
    * Active/désactive un utilisateur
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function toggleStatus($id)
   {
      $user = User::findOrFail($id);

      // Empêcher la désactivation de l'utilisateur connecté
      if ($user->id === auth()->id()) {
         return $this->back()
            ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
      }

      $user->update([
         'is_active' => !$user->is_active,
      ]);

      $status = $user->is_active ? 'activé' : 'désactivé';
      return $this->back()
         ->with('success', "L'utilisateur a été {$status} avec succès.");
   }
}
