<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class ProductController extends Controller
{
   public function index(Request $request): Response
   {
      $products = Product::all();

      return $this->view('products.index', [
         'title' => 'Liste des produits',
         'products' => $products
      ]);
   }

   public function create(Request $request): Response
   {
      $categories = Category::all()->pluck('name', 'id');

      $form = Product::form()->input('name', 'Nom du produit')
         ->textarea('description', 'Description du produit')
         ->input('price', 'Prix du produit', ['type' => 'number', 'step' => '0.01'])
         ->input('stock', 'Quantité en stock', ['type' => 'number'])
         ->select('category_id', 'Catégorie du produit', $categories)
         ->button('Créer le produit')
         ->action('/products/store');

      return $this->view('products.create', [
         'title' => 'Créer un produit',
         'form' => $form->render()
      ]);
   }

   public function store(Request $request): Response
   {
      $data = $request->validate([
         'name' => 'required|string|max:255',
         'description' => 'nullable|string',
         'price' => 'required|numeric|min:0',
         'stock' => 'required|integer|min:0',
         'category_id' => 'required|exists:categories,id'
      ]);

      $product = Product::create($data);

      return $this->redirect('/products')->with('success', 'Produit créé avec succès');
   }

   public function show(Request $request, $id): Response
   {
      $product = Product::findOrFail($id);

      return $this->view('products.show', [
         'title' => $product->name,
         'product' => $product
      ]);
   }

   public function edit(Request $request, $id): Response
   {
      $product = Product::findOrFail($id);
      $categories = Category::all()->pluck('name', 'id');

      $form = Product::form()->fill($product->toArray())
         ->input('name', 'Nom du produit')
         ->textarea('description', 'Description du produit')
         ->input('price', 'Prix du produit', ['type' => 'number', 'step' => '0.01'])
         ->input('stock', 'Quantité en stock', ['type' => 'number'])
         ->select('category_id', 'Catégorie du produit', $categories)
         ->button('Modifier le produit')
         ->action("/products/update/{$id}");

      return $this->view('products.edit', [
         'title' => 'Modifier le produit: ' . $product->name,
         'product' => $product,
         'form' => $form->render()
      ]);
   }

   public function update(Request $request, $id): Response
   {
      $product = Product::findOrFail($id);

      $data = $request->validate([
         'name' => 'required|string|max:255',
         'description' => 'nullable|string',
         'price' => 'required|numeric|min:0',
         'stock' => 'required|integer|min:0',
         'category_id' => 'required|exists:categories,id'
      ]);

      $product->fill($data);
      $product->save();

      return $this->redirect('/products')->with('success', 'Produit mis à jour avec succès');
   }

   public function destroy(Request $request, $id): Response
   {
      $product = Product::findOrFail($id);
      $product->delete();

      return $this->redirect('/products')->with('success', 'Produit supprimé avec succès');
   }
}
