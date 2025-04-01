<?php

namespace App\Controllers\CraftPanel;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use App\Models\Role;
use App\Models\Permission;
use IronFlow\Validation\Validator;

class RoleController extends Controller
{

   public function index(Request $request): Response
   {
      $roles = Role::with('permissions')->paginate(10);
      return $this->view('craftpanel.roles.index', compact('roles'));
   }


   public function create(Request $request): Response
   {
      $permissions = Permission::all();
      return $this->view('craftpanel.roles.create', compact('permissions'));
   }


   public function store(Request $request): Response
   {
      $validator = Validator::make($request->all(), [
         'name' => 'required|string|max:255|unique:roles',
         'description' => 'nullable|string',
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return $this->redirect()->back()->withErrors($validator->errors())->withInput();
      }

      $role = Role::create([
         'name' => $request->input('name'),
         'description' => $request->input('description'),
      ]);

      if ($request->has('permissions')) {
         $role->permissions()->sync($request->input('permissions'));
      }

      return $this->redirect('craftpanel.roles.index')->with('success', 'Le rôle a été créé avec succès.');
   }

   public function edit(Request $request, $id): Response
   {
      $role = Role::findOrFail($id);
      $permissions = Permission::all();
      return $this->view('craftpanel.roles.edit', compact('role', 'permissions'));
   }


   public function update(Request $request, $id): Response
   {
      $role = Role::findOrFail($id);

      $validator = Validator::make($request->all(), [
         'name' => 'required|string|max:255|unique:roles,name,' . $id,
         'description' => 'nullable|string',
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return $this->redirect()->back()->withErrors($validator->errors())->withInput();
      }

      $role->update([
         'name' => $request->input('name'),
         'description' => $request->input('description'),
      ]);

      if ($request->has('permissions')) {
         $role->permissions()->sync($request->input('permissions'));
      }

      return $this->redirect('craftpanel.roles.index')->with('success', 'Le rôle a été mis à jour avec succès.');
   }


   public function destroy(Request $request, $id): Response
   {
      $role = Role::findOrFail($id);

      // Vérifier si le rôle est utilisé
      if ($role->users()->count() > 0) {
         return $this->redirect()->back()->with('error', 'Ce rôle ne peut pas être supprimé car il est attribué à des utilisateurs.');
      }

      $role->delete();

      return $this->redirect('craftpanel.roles.index')->with('success', 'Le rôle a été supprimé avec succès.');
   } 


   public function updatePermissions(Request $request, $id): Response
   {
      $role = Role::findOrFail($id);

      $validator = Validator::make($request->all(), [
         'permissions' => 'array',
         'permissions.*' => 'exists:permissions,id',
      ]);

      if ($validator->fails()) {
         return $this->redirect()->back()->withErrors($validator->errors())->withInput();
      }

      $role->permissions()->sync($request->input('permissions', []));

      return $this->redirect()->back()->with('success', 'Les permissions ont été mises à jour avec succès.');
   }
}
