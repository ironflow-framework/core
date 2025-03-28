<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class ProductController extends Controller
{
   public function index(Request $request): Response
   {
      return $this->view('products.index', [
         'title' => 'Liste des produits'
      ]);
   }

   public function create(Request $request): Response
   {
      $form = Product::form()->input('name', 'Nom du produit')
         ->input('description', 'Description du produit')
         ->input('price', 'Prix du produit')
         ->input('stock', 'Quantité en stock')
         ->input('category', 'Catégorie du produit')
         ->button('Créer le produit')
         ->action('/products/store');

      return $this->view('products.create', [
         'title' => 'Créer un produit',
         'form' => $form->render()
      ]);
   }

   public function store(Request $request): Response
   {
      // TODO: Implémenter la création
      return $this->redirect('/products');
   }

   public function show(Request $request, $id): Response
   {
      return $this->view('products.show', [
         'title' => 'Détails du produit',
         'id' => $id
      ]);
   }

   public function edit(Request $request, $id): Response
   {
      return $this->view('products.edit', [
         'title' => 'Modifier le produit',
         'id' => $id
      ]);
   }

   public function update(Request $request, $id): Response
   {
      // TODO: Implémenter la mise à jour
      return $this->redirect('/products');
   }

   public function destroy(Request $request, $id): Response
   {
      // TODO: Implémenter la suppression
      return $this->redirect('/products');
   }
}
