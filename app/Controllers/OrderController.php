<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\Product;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class OrderController extends Controller
{
   public function index(Request $request): Response
   {
      $orders = Order::all();

      return $this->view('orders.index', [
         'title' => 'Liste des commandes',
         'orders' => $orders
      ]);
   }

   public function create(Request $request): Response
   {
      $products = Product::all();

      return $this->view('orders.create', [
         'title' => 'Nouvelle commande',
         'products' => $products
      ]);
   }

   public function store(Request $request): Response
   {
      $data = $request->validate([
         'user_id' => 'required|integer',
         'shipping_address' => 'required|string',
         'billing_address' => 'required|string',
         'payment_method' => 'required|string',
         'shipping_method' => 'required|string',
         'notes' => 'nullable|string',
         'products' => 'required|array',
         'products.*.id' => 'required|exists:products,id',
         'products.*.quantity' => 'required|integer|min:1'
      ]);

      // Créer la commande
      $order = Order::create([
         'user_id' => $data['user_id'],
         'status' => 'pending',
         'shipping_address' => $data['shipping_address'],
         'billing_address' => $data['billing_address'],
         'payment_method' => $data['payment_method'],
         'shipping_method' => $data['shipping_method'],
         'notes' => $data['notes'] ?? null
      ]);

      // Ajouter les produits à la commande
      foreach ($data['products'] as $item) {
         $product = Product::findOrFail($item['id']);
         $order->addProduct($product, $item['quantity']);

         // Mettre à jour le stock du produit
         $product->stock -= $item['quantity'];
         $product->save();
      }

      return $this->redirect('/orders')->with('success', 'Commande créée avec succès');
   }

   public function show(Request $request, $id): Response
   {
      $order = Order::findOrFail($id);

      return $this->view('orders.show', [
         'title' => 'Commande #' . $order->id,
         'order' => $order
      ]);
   }

   public function updateStatus(Request $request, $id): Response
   {
      $order = Order::findOrFail($id);

      $data = $request->validate([
         'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled'
      ]);

      $order->status = $data['status'];
      $order->save();

      return $this->redirect('/orders/' . $id)->with('success', 'Statut de la commande mis à jour');
   }

   public function destroy(Request $request, $id): Response
   {
      $order = Order::findOrFail($id);

      // Si la commande n'est pas livrée, remettre en stock les produits
      if ($order->status !== 'delivered') {
         foreach ($order->products as $product) {
            $product->stock += $product->pivot->quantity;
            $product->save();
         }
      }

      $order->delete();

      return $this->redirect('/orders')->with('success', 'Commande supprimée avec succès');
   }
}
