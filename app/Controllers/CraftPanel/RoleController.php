<?php

namespace App\Http\Controllers\CraftPanel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Redirect;
use IronFlow\Support\Facades\Request;
use IronFlow\Support\Facades\Validator;

class RoleController extends Controller
{
   /**
    * Affiche la liste des rôles
    *
    * @return \IronFlow\Support\Facades\View
    */
   public function index()
   {
      $roles = Role::with('permissions')->paginate(10);
      return View::make('craftpanel.roles.index', compact('roles'));
   }

   /**
    * Affiche le formulaire de création de rôle
    *
    * @return \IronFlow\Support\Facades\View
    */
   public function create()
   {
      $permissions = Permission::all();
      return View::make('craftpanel.roles.create', compact('permissions'));
   }

   /**
    * Enregistre un nouveau rôle
    *
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function store()
   {
      $validator = Validator::make(Request::all(), [
         'name' => 'required|string|max:255|unique:roles',
         'description' => 'nullable|string',
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return Redirect::back()
            ->withErrors($validator)
            ->withInput();
      }

      $role = Role::create([
         'name' => Request::input('name'),
         'description' => Request::input('description'),
      ]);

      if (Request::has('permissions')) {
         $role->permissions()->sync(Request::input('permissions'));
      }

      return Redirect::route('craftpanel.roles.index')
         ->with('success', 'Le rôle a été créé avec succès.');
   }

   /**
    * Affiche le formulaire de modification de rôle
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\View
    */
   public function edit($id)
   {
      $role = Role::findOrFail($id);
      $permissions = Permission::all();
      return View::make('craftpanel.roles.edit', compact('role', 'permissions'));
   }

   /**
    * Met à jour un rôle
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function update($id)
   {
      $role = Role::findOrFail($id);

      $validator = Validator::make(Request::all(), [
         'name' => 'required|string|max:255|unique:roles,name,' . $id,
         'description' => 'nullable|string',
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return Redirect::back()
            ->withErrors($validator)
            ->withInput();
      }

      $role->update([
         'name' => Request::input('name'),
         'description' => Request::input('description'),
      ]);

      if (Request::has('permissions')) {
         $role->permissions()->sync(Request::input('permissions'));
      }

      return Redirect::route('craftpanel.roles.index')
         ->with('success', 'Le rôle a été mis à jour avec succès.');
   }

   /**
    * Supprime un rôle
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function destroy($id)
   {
      $role = Role::findOrFail($id);

      // Vérifier si le rôle est utilisé
      if ($role->users()->count() > 0) {
         return Redirect::back()
            ->with('error', 'Ce rôle ne peut pas être supprimé car il est attribué à des utilisateurs.');
      }

      $role->delete();

      return Redirect::route('craftpanel.roles.index')
         ->with('success', 'Le rôle a été supprimé avec succès.');
   }

   /**
    * Met à jour les permissions d'un rôle
    *
    * @param  int  $id
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function updatePermissions($id)
   {
      $role = Role::findOrFail($id);

      $validator = Validator::make(Request::all(), [
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return Redirect::back()
            ->withErrors($validator)
            ->withInput();
      }

      $role->permissions()->sync(Request::input('permissions', []));

      return Redirect::back()
         ->with('success', 'Les permissions ont été mises à jour avec succès.');
   }
}
