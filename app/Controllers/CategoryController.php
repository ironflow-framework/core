<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Validation\Validator;

class CategoryController extends Controller
{
   public function index(Request $request): Response
   {
      $categories = Category::all();

      return $this->view('categories.index', [
         'title' => 'Liste des catégories',
         'categories' => $categories
      ]);
   }

   public function create(Request $request): Response
   {
      $form = Category::form()
         ->method('POST')
         ->action(route('categories.store'))
         ->theme('floating')
         ->input('name', 'Nom de la catégorie', [
            'required' => true,
            'placeholder' => 'Entrez le nom de la catégorie'
         ])
         ->textarea('description', 'Description', [
            'rows' => 3,
            'placeholder' => 'Entrez une description pour la catégorie'
         ])
         ->checkbox('is_active', 'Actif',  [
            'checked' => true
         ])
         ->radio('type', 'Type', [
            'options' => [
               'product' => 'Produit',
               'service' => 'Service'
            ],
            'value' => 'product'
         ])
         ->button('Créer la catégorie', [
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus'
         ]);

      return $this->view('categories.create', [
         'title' => 'Créer une catégorie',
         'form' => $form->render()
      ]);
   }

   public function store(Request $request): Response
   {
      if ($request->isMethod('post')) {
         $data = $request->all();

         $validator = Validator::make($data, [
            'name' => ['required', 'stringLength:min=1,max=255'],
            'description' => ['nullable', 'stringLength:max=1000'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'type' => ['required', 'in:product,service'],
         ]);

         if ($validator->fails()) {
            return $this->back()->withErrors($validator->getErrors())->withInput();
         }

         $category = Category::create($data);

         return $this->route('categories.index')
            ->with([
               'success' => 'Catégorie créée avec succès',
               'category' => $category
            ]);
      }

      return $this->back();
   }

   public function show(Request $request, $id): Response
   {
      $category = Category::with('parent')->find($id);

      if (!$category) {
         return $this->route('categories.index')->with(['error' => 'Catégorie non trouvée']);
      }

      return $this->view('categories.show', [
         'title' => $category->name,
         'category' => $category
      ]);
   }

   public function edit(Request $request, $id): Response
   {
      $category = Category::find($id);

      if (!$category) {
         return $this->route('categories.index')->with(['error' => 'Catégorie non trouvée']);
      }

      $form = Category::form()
         ->method('POST')
         ->action(route('categories.update', ['id' => $id]))
         ->theme('floating')
         ->fill($category->toArray())
         ->input('name', 'Nom de la catégorie', [
            'required' => true,
            'placeholder' => 'Entrez le nom de la catégorie'
         ])
         ->textarea('description', 'Description', [
            'rows' => 3,
            'placeholder' => 'Entrez une description pour la catégorie'
         ])
         ->checkbox('is_active', 'Actif',  [
            'checked' => $category->is_active
         ])
         ->radio('type', 'Type', [
            'options' => [
               'product' => 'Produit',
               'service' => 'Service'
            ],
            'value' => $category->type
         ])
         ->button('Modifier la catégorie', [
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-save'
         ]);

      return $this->view('categories.edit', [
         'title' => 'Modifier la catégorie: ' . $category->name,
         'category' => $category,
         'form' => $form->render()
      ]);
   }

   public function update(Request $request, $id): Response
   {
      if ($request->isMethod('post')) {
         $category = Category::find($id);

         if (!$category) {
            return $this->route('categories.index')->with(['error' => 'Catégorie non trouvée']);
         }

         $data = $request->all();

         $validator = Validator::make($data, [
            'name' => ['required', 'stringLength:min=1,max=255'],
            'description' => ['nullable', 'stringLength:max=1000'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'type' => ['required', 'in:product,service'],
         ]);

         if ($validator->fails()) {
            return $this->back()->withErrors($validator->getErrors())->withInput();
         }

         $category->fill($data);
         $category->save();

         return $this->route('categories.index')->with(['success' => 'Catégorie mise à jour avec succès']);
      }

      return $this->back();
   }

   public function destroy(Request $request, $id): Response
   {
      if ($request->isMethod('POST')) {
         $category = Category::find($id);

         if (!$category) {
            return $this->route('categories.index')->with(['error' => 'Catégorie non trouvée']);
         }

         $category->delete();

         return $this->route('categories.index')->with(['success' => 'Catégorie supprimée avec succès']);
      }

      return $this->back();
   }
}
