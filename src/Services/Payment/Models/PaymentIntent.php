<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\BelongsTo;

/**
 * Modèle représentant une intention de paiement
 */
class PaymentIntent extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_intents';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'customer_id',
      'provider',
      'provider_intent_id',
      'amount',
      'currency',
      'payment_method_id',
      'description',
      'status',
      'client_secret',
      'metadata',
      'error_message',
      'next_action',
      'canceled_at',
      'expires_at',
   ];

   /**
    * Attributs cachés
    *
    * @var array
    */
   protected array $hidden = [
      'client_secret',
      'metadata',
   ];

   /**
    * Conversions de types pour les attributs
    *
    * @var array
    */
   protected array $casts = [
      'amount' => 'float',
      'status' => 'string',
      'metadata' => 'json',
      'next_action' => 'json',
      'canceled_at' => 'datetime',
      'expires_at' => 'datetime',
   ];

   /**
    * Récupère le client associé à cette intention de paiement
    *
    * @return BelongsTo
    */
   public function customer(): BelongsTo
   {
      return $this->belongsTo(Customer::class, 'customer_id');
   }

   /**
    * Récupère la méthode de paiement associée
    *
    * @return BelongsTo
    */
   public function paymentMethod(): BelongsTo
   {
      return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
   }

   /**
    * Vérifie si l'intention de paiement est en attente
    *
    * @return bool
    */
   public function isPending(): bool
   {
      return in_array($this->status, ['requires_payment_method', 'requires_confirmation', 'requires_action']);
   }

   /**
    * Vérifie si l'intention de paiement est réussie
    *
    * @return bool
    */
   public function isSucceeded(): bool
   {
      return $this->status === 'succeeded';
   }

   /**
    * Vérifie si l'intention de paiement est annulée
    *
    * @return bool
    */
   public function isCancelled(): bool
   {
      return $this->status === 'canceled';
   }

   /**
    * Vérifie si l'intention de paiement a échoué
    *
    * @return bool
    */
   public function isFailed(): bool
   {
      return in_array($this->status, ['requires_payment_method', 'canceled']) && !empty($this->error_message);
   }

   /**
    * Vérifie si l'intention de paiement est expirée
    *
    * @return bool
    */
   public function isExpired(): bool
   {
      if (!$this->expires_at) {
         return false;
      }

      return $this->expires_at->isPast();
   }
}
