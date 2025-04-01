<?php

namespace App\Models;

use IronFlow\Database\Factories\HasFactory;
use IronFlow\Database\Model;
use IronFlow\Forms\Furnace\HasForm;

class Order extends Model
{
   use HasFactory;
   use HasForm;

   protected static string $table = 'orders';

   protected array $fillable = [
      'user_id',
      'total_amount',
      'status',
      'shipping_address',
      'billing_address',
      'payment_method',
      'shipping_method',
      'notes'
   ];

   protected array $casts = [
      'total_amount' => 'float',
      'created_at' => 'datetime',
      'updated_at' => 'datetime'
   ];

   /**
    * Les produits associés à cette commande
    */
   public function products()
   {
      return $this->belongsToMany(Product::class, 'order_products')
         ->withPivot(['quantity', 'price']);
   }

   /**
    * Ajouter un produit à la commande
    */
   public function addProduct(Product $product, int $quantity = 1, ?float $price = null): self
   {
      $price = $price ?? $product->price;

      $this->products()->attach($product->id, [
         'quantity' => $quantity,
         'price' => $price
      ]);

      $this->updateTotalAmount();

      return $this;
   }

   /**
    * Mettre à jour la quantité d'un produit dans la commande
    */
   public function updateProductQuantity(Product $product, int $quantity): self
   {
      if ($quantity <= 0) {
         $this->removeProduct($product);
         return $this;
      }

      $this->products()->sync([$product->id => ['quantity' => $quantity]]);

      $this->updateTotalAmount();

      return $this;
   }

   /**
    * Supprimer un produit de la commande
    */
   public function removeProduct(Product $product): self
   {
      $this->products()->detach($product->id);

      $this->updateTotalAmount();

      return $this;
   }

   /**
    * Mettre à jour le montant total de la commande
    */
   protected function updateTotalAmount(): void
   {
      $total = 0;

      foreach ($this->products as $product) {
         $total += $product->pivot->price * $product->pivot->quantity;
      }

      $this->total_amount = $total;
      $this->save();
   }
}
