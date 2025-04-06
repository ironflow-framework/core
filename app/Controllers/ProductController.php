<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Validation\Validator;

class ProductController extends Controller
{
   public function index(Request $request): Response
   {
      $products = Product::with('category')->get();

      return $this->view('products.index', [
         'title' => 'Liste des produits',
         'products' => $products
      ]);
   }

   public function create(Request $request): Response
   {
      $form = Product::form()
         ->method('POST')
         ->action(route('products.store'))
         ->theme('floating')
         ->title('Creation d\'un produit')
         ->input('name', 'Nom du produit', [
            'required' => true,
            'placeholder' => 'Entrez le nom du produit'
         ])
         ->textarea('description', 'Description', [
            'rows' => 3,
            'placeholder' => 'Entrez une description pour le produit'
         ])
         ->input('price', 'Prix', [
            'type' => 'number',
            'step' => '0.01',
            'required' => true,
            'placeholder' => 'Entrez le prix du produit'
         ])
         ->input('stock', 'Stock', [
            'type' => 'number',
            'required' => true,
            'placeholder' => 'Entrez la quantité en stock'
         ])
         ->select('category_id', 'Catégorie', ['' => 'Sélectionnez une catégorie'] + Category::pluck(['name', 'id'])->toArray(),
         [  
         'required' => true
         ])
         ->checkbox('is_active', 'Actif', [], [
            'checked' => true
         ])
         ->button('Créer le produit', [
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus'
         ]);

      return $this->view('products.create', [
         'title' => 'Créer un produit',
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
            'price' => ['required', 'numeric:min=0'],
            'stock' => ['required', 'number:min=0'],
            'category_id' => ['required', 'exists:categories,id'],
         ]);

         if ($validator->fails()) {
            return $this->back()->withErrors($validator->getErrors())->withInput();
         }

         $product = Product::create($data);

         return $this->route('products.index')
            ->with([
               'success' => 'Produit créé avec succès',
               'product' => $product
            ]);
      }

      return $this->back();
   }

   public function show(Request $request, $id): Response
   {
      $product = Product::with('category')->find($id);

      if (!$product) {
         return $this->route('products.index')->with(['error' => 'Produit non trouvé']);
      }

      return $this->view('products.show', [
         'title' => $product->name,
         'product' => $product
      ]);
   }

   public function edit(Request $request, $id): Response
   {
      $product = Product::find($id);

      if (!$product) {
         return $this->route('products.index')->with(['error' => 'Produit non trouvé']);
      }

      $form = Product::form()
         ->method('post')
         ->action(route('products.update', ['id' => $id]))
         ->theme('floating')
         ->title('Modifier un produit')
         ->fill($product->toArray())
         ->input('name', 'Nom du produit', [
            'required' => true,
            'placeholder' => 'Entrez le nom du produit'
         ])
         ->textarea('description', 'Description', [
            'rows' => 3,
            'placeholder' => 'Entrez une description pour le produit'
         ])
         ->input('price', 'Prix', [
            'type' => 'number',
            'step' => '0.01',
            'min' => '0',
            'required' => true,
            'placeholder' => 'Entrez le prix du produit'
         ])
         ->input('stock', 'Stock', [
            'type' => 'number',
            'min' => '0',
            'required' => true,
            'placeholder' => 'Entrez la quantité en stock'
         ])
         ->select(
            'category_id',
            'Catégorie',
            Category::pluck(['name', 'id'])->toArray(),
            [
               'required' => true
            ]
         )
         ->button('Modifier le produit', [
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-save'
         ]);

      return $this->view('products.edit', [
         'title' => 'Modifier le produit: ' . $product->name,
         'product' => $product,
         'form' => $form->render()
      ]);
   }

   public function update(Request $request, $id): Response
   {
      if ($request->isMethod('post')) {
         $product = Product::find($id);

         if (!$product) {
            return $this->route('products.index')->with(['error' => 'Produit non trouvé']);
         }

         $data = $request->all();

         $validator = Validator::make($data, [
            'name' => ['required', 'stringLength:min=1,max=255'],
            'description' => ['nullable', 'stringLength:max=1000'],
            'price' => ['required', 'numeric:min=0'],
            'stock' => ['required', 'number:min=0'],
            'category_id' => ['required', 'exists:categories,id'],
            
         ]);

         if ($validator->fails()) {
            dump($validator->getErrors());
            return $this->back()->withErrors($validator->getErrors())->withInput();
         }

         $product->fill($data);
         $product->save();

         return $this->route('products.index')->with(['success' => 'Produit mis à jour avec succès']);
      }

      return $this->back();
   }

   public function destroy(Request $request, $id): Response
   {
      if ($request->isMethod('POST')) {
         $product = Product::find($id);

         if (!$product) {
            return $this->route('products.index')->with(['error' => 'Produit non trouvé']);
         }

         $product->remove();

         return $this->route('products.index')->with(['success' => 'Produit supprimé avec succès']);
      }

      return $this->back();
   }
}
