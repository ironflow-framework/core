<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Controllers;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\CraftPanel\Models\AdminRole;
use IronFlow\CraftPanel\Models\AdminPermission;

/**
 * Contrôleur pour la gestion des rôles et permissions
 */
class RoleController extends CraftPanelController
{
   /**
    * Affiche la liste des rôles
    *
    * @param Request $request
    * @return Response
    */
   public function index(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.view')) {
         return $this->redirect('dashboard');
      }

      $roles = AdminRole::with('permissions')->get();

      return $this->view('craftpanel::roles.index', [
         'roles' => $roles
      ]);
   }

   /**
    * Affiche le formulaire de création d'un rôle
    *
    * @param Request $request
    * @return Response
    */
   public function create(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.create')) {
         return $this->redirect('dashboard');
      }

      $permissions = AdminPermission::all()->groupBy('group');

      return $this->view('craftpanel::roles.create', [
         'permissions' => $permissions
      ]);
   }

   /**
    * Enregistre un nouveau rôle
    *
    * @param Request $request
    * @return Response
    */
   public function store(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.create')) {
         return $this->redirect('dashboard');
      }

      // Validation des données
      $validated = $request->validate([
         'name' => 'required|string|max:255|unique:admin_roles,name',
         'description' => 'nullable|string',
         'permissions' => 'required|array'
      ]);

      // Création du rôle
      $role = new AdminRole();
      $role->name = $validated['name'];
      $role->description = $validated['description'] ?? '';
      $role->save();

      // Association des permissions
      $role->permissions()->attach($validated['permissions']);

      // Journalisation de l'activité
      $this->logActivity('Création du rôle: ' . $role->name);

      return $this->redirect('roles.index')->with('success', 'Rôle créé avec succès');
   }

   /**
    * Affiche le formulaire d'édition d'un rôle
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function edit(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.edit')) {
         return $this->redirect('dashboard');
      }

      $role = AdminRole::with('permissions')->find($id);
      if (!$role) {
         return $this->redirect('roles.index')->with('error', 'Rôle non trouvé');
      }

      $permissions = AdminPermission::all()->groupBy('group');
      $rolePermissions = $role->permissions->pluck('id')->toArray();

      return $this->view('craftpanel::roles.edit', [
         'role' => $role,
         'permissions' => $permissions,
         'rolePermissions' => $rolePermissions
      ]);
   }

   /**
    * Met à jour un rôle
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function update(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.edit')) {
         return $this->redirect('dashboard');
      }

      $role = AdminRole::find($id);
      if (!$role) {
         return $this->redirect('roles.index')->with('error', 'Rôle non trouvé');
      }

      // Validation des données
      $validated = $request->validate([
         'name' => 'required|string|max:255|unique:admin_roles,name,' . $id,
         'description' => 'nullable|string',
         'permissions' => 'required|array'
      ]);

      // Mise à jour du rôle
      $role->name = $validated['name'];
      $role->description = $validated['description'] ?? '';
      $role->save();

      // Mise à jour des permissions
      $role->permissions()->sync($validated['permissions']);

      // Journalisation de l'activité
      $this->logActivity('Mise à jour du rôle: ' . $role->name);

      return $this->redirect('roles.index')->with('success', 'Rôle mis à jour avec succès');
   }

   /**
    * Supprime un rôle
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function destroy(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('roles.delete')) {
         return $this->redirect('dashboard');
      }

      $role = AdminRole::find($id);
      if (!$role) {
         return $this->redirect('roles.index')->with('error', 'Rôle non trouvé');
      }

      // Vérification si le rôle est associé à des utilisateurs
      if ($role->users()->count() > 0) {
         return $this->redirect('roles.index')->with('error', 'Ce rôle est associé à des utilisateurs et ne peut pas être supprimé');
      }

      // Journalisation avant suppression
      $this->logActivity('Suppression du rôle: ' . $role->name);

      // Suppression des relations de permissions
      $role->permissions()->detach();

      // Suppression du rôle
      $role->delete();

      return $this->redirect('roles.index')->with('success', 'Rôle supprimé avec succès');
   }

   /**
    * Journalise une activité
    *
    * @param string $description
    * @return void
    */
   protected function logActivity(string $description): void
   {
      $activity = new \IronFlow\CraftPanel\Models\AdminActivityLog();
      $activity->user_id = $this->currentUser->id;
      $activity->action = debug_backtrace()[1]['function'];
      $activity->description = $description;
      $activity->ip_address = request()->ip();
      $activity->user_agent = request()->userAgent();
      $activity->save();
   }
}
