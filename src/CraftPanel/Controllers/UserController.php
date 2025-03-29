<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Controllers;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\CraftPanel\Models\AdminUser;
use IronFlow\CraftPanel\Models\AdminRole;
use IronFlow\Support\Validation\Validator;

/**
 * Contrôleur pour la gestion des utilisateurs du CraftPanel
 */
class UserController extends CraftPanelController
{
   /**
    * Affiche la liste des utilisateurs
    *
    * @param Request $request
    * @return Response
    */
   public function index(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.view')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      $users = AdminUser::all();

      return $this->view('craftpanel::users.index', [
         'users' => $users
      ]);
   }

   /**
    * Affiche le formulaire de création d'un utilisateur
    *
    * @param Request $request
    * @return Response
    */
   public function create(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.create')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      $roles = AdminRole::all();

      return $this->view('craftpanel::users.create', [
         'roles' => $roles
      ]);
   }

   /**
    * Enregistre un nouvel utilisateur
    *
    * @param Request $request
    * @return Response
    */
   public function store(Request $request): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.create')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      // Validation des données
      $validator = new Validator($request->all(), [
         'name' => 'required|string|max:255',
         'email' => 'required|email|unique:admin_users,email',
         'password' => 'required|string|min:8|confirmed',
         'role_id' => 'required|exists:admin_roles,id'
      ]);

      if ($validator->fails()) {
         return $this->redirect('users.create')->withErrors($validator->errors());
      }

      // Création de l'utilisateur
      $user = new AdminUser();
      $user->name = $request->input('name');
      $user->email = $request->input('email');
      $user->password = bcrypt($request->input('password'));
      $user->save();

      // Association au rôle
      $user->roles()->attach($request->input('role_id'));

      // Journalisation de l'activité
      $this->logActivity('Création d\'un nouvel administrateur: ' . $user->name);

      return $this->redirect('users.index')->with('success', 'Utilisateur créé avec succès');
   }

   /**
    * Affiche le formulaire d'édition d'un utilisateur
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function edit(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.edit')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      $user = AdminUser::find($id);
      if (!$user) {
         return $this->redirect('users.index')->withErrors(['Utilisateur non trouvé']);
      }

      $roles = AdminRole::all();

      return $this->view('craftpanel::users.edit', [
         'user' => $user,
         'roles' => $roles,
         'userRoles' => $user->roles->pluck('id')->toArray()
      ]);
   }

   /**
    * Met à jour un utilisateur
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function update(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.edit')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      $user = AdminUser::find($id);
      if (!$user) {
         return $this->redirect('users.index')->withErrors(['Utilisateur non trouvé']);
      }

      // Validation des données
      $rules = [
         'name' => 'required|string|max:255',
         'email' => 'required|email|unique:admin_users,email,' . $id,
         'role_id' => 'required|exists:admin_roles,id'
      ];

      // Ajout de la validation du mot de passe uniquement s'il est fourni
      if ($request->filled('password')) {
         $rules['password'] = 'string|min:8|confirmed';
      }

      $validator = new Validator($request->all(), $rules);

      if ($validator->fails()) {
         return $this->redirect('users.edit', ['id' => $id])->withErrors($validator->errors());
      }

      // Mise à jour de l'utilisateur
      $user->name = $request->input('name');
      $user->email = $request->input('email');

      if ($request->filled('password')) {
         $user->password = bcrypt($request->input('password'));
      }

      $user->save();

      // Mise à jour des rôles
      $user->roles()->sync([$request->input('role_id')]);

      // Journalisation de l'activité
      $this->logActivity('Mise à jour de l\'administrateur: ' . $user->name);

      return $this->redirect('users.index')->with('success', 'Utilisateur mis à jour avec succès');
   }

   /**
    * Supprime un utilisateur
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function destroy(Request $request, int $id): Response
   {
      // Vérification des permissions
      if (!$this->hasPermission('users.delete')) {
         return $this->redirect('dashboard')->withErrors(['Vous n\'avez pas les permissions nécessaires']);
      }

      $user = AdminUser::find($id);
      if (!$user) {
         return $this->redirect('users.index')->withErrors(['Utilisateur non trouvé']);
      }

      // Protection contre la suppression de son propre compte
      if ($user->id === $this->currentUser->id) {
         return $this->redirect('users.index')->withErrors(['Vous ne pouvez pas supprimer votre propre compte']);
      }

      // Journalisation avant suppression
      $this->logActivity('Suppression de l\'administrateur: ' . $user->name);

      // Suppression des relations de rôles
      $user->roles()->detach();

      // Suppression de l'utilisateur
      $user->delete();

      return $this->redirect('users.index')->with('success', 'Utilisateur supprimé avec succès');
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
