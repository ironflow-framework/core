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
         ->input('name', 'Nom de la catégorie')
         ->theme('floating')
         ->button('Créer la catégorie');

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
      $category = Category::findOrFail($id);

      return $this->view('categories.show', [
         'title' => $category->name,
         'category' => $category
      ]);
   }

   public function edit(Request $request, $id): Response
   {
      $category = Category::findOrFail($id);

      $form = Category::form()->fill($category->toArray())
         ->input('name', 'Nom de la catégorie')
         ->button('Modifier la catégorie')
         ->action(route('categories.update.alt', ['id' => $id]));

      return $this->view('categories.edit', [
         'title' => 'Modifier la catégorie: ' . $category->name,
         'category' => $category,
         'form' => $form->render()
      ]);
   }

   public function update(Request $request, $id): Response
   {
      if ($request->isMethod('post')) {
         $category = Category::findOrFail($id);
         $data = $request->all();

         $validator = Validator::make($data, [
            'name' => ['required', 'stringLength:min=1,max=255'],
         ]);

         if (!$validator->fails()) {
            return $this->back()->withErrors($validator->getErrors())->withInput();
         }

         $category->fill($data);
         $category->save();

         return $this->route('categories.index')->with(['success' => 'Catégorie mis à jour avec succès']);
      }

      return $this->back();
   }

   public function destroy(Request $request, $id): Response
   {
      if ($request->isMethod('POST')) {
         $category = Category::findOrFail($id);
         $category->delete();

         return $this->route('categories.index')->with(['success' => 'Catégorie supprimée avec succès']);
      }

      return $this->back();
   }
}
