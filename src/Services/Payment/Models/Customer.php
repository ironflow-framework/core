<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\HasMany;

/**
 * Modèle représentant un client de paiement
 */
class Customer extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_customers';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'user_id',
      'provider',
      'provider_customer_id',
      'name',
      'email',
      'phone',
      'address',
      'city',
      'state',
      'country',
      'postal_code',
      'metadata',
      'default_payment_method_id',
   ];

   /**
    * Attributs cachés
    *
    * @var array
    */
   protected array $hidden = [
      'metadata',
   ];

   /**
    * Conversions de types pour les attributs
    *
    * @var array
    */
   protected array $casts = [
      'metadata' => 'json',
   ];

   /**
    * Récupère les méthodes de paiement du client
    *
    * @return HasMany
    */
   public function paymentMethods(): HasMany
   {
      return $this->hasMany(PaymentMethod::class, 'customer_id');
   }

   /**
    * Récupère les abonnements du client
    *
    * @return HasMany
    */
   public function subscriptions(): HasMany
   {
      return $this->hasMany(Subscription::class, 'customer_id');
   }

   /**
    * Récupère les transactions du client
    *
    * @return HasMany
    */
   public function transactions(): HasMany
   {
      return $this->hasMany(Transaction::class, 'customer_id');
   }

   /**
    * Récupère la méthode de paiement par défaut du client
    *
    * @return PaymentMethod|null
    */
   public function defaultPaymentMethod(): ?PaymentMethod
   {
      return $this->default_payment_method_id
         ? PaymentMethod::find($this->default_payment_method_id)
         : null;
   }

   /**
    * Vérifie si le client a un abonnement actif à un plan donné
    *
    * @param string $planId Identifiant du plan
    * @return bool
    */
   public function hasActiveSubscriptionTo(string $planId): bool
   {
      return $this->subscriptions()
         ->where('plan_id', $planId)
         ->where('status', 'active')
         ->exists();
   }

   /**
    * Récupère l'abonnement actif d'un client pour un plan donné
    *
    * @param string $planId Identifiant du plan
    * @return Subscription|null
    */
   public function getActiveSubscriptionTo(string $planId): ?Subscription
   {
      return $this->subscriptions()
         ->where('plan_id', $planId)
         ->where('status', 'active')
         ->first();
   }
}
