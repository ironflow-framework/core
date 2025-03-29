<?php

declare(strict_types=1);

namespace IronFlow\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Relations\BelongsTo;
use IronFlow\Database\Relations\HasMany;

/**
 * Modèle représentant une transaction de paiement
 */
class Transaction extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_transactions';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'customer_id',
      'subscription_id',
      'provider',
      'provider_transaction_id',
      'provider_payment_method_id',
      'amount',
      'currency',
      'description',
      'status',
      'type',
      'reference',
      'refunded_amount',
      'metadata',
      'error_message',
      'error_code',
   ];

   /**
    * Attributs cachés
    *
    * @var array
    */
   protected array $hidden = [
      'metadata',
      'error_message',
      'error_code',
   ];

   /**
    * Conversions de types pour les attributs
    *
    * @var array
    */
   protected array $casts = [
      'amount' => 'float',
      'refunded_amount' => 'float',
      'metadata' => 'json',
   ];

   /**
    * Liste des statuts possibles
    */
   const STATUS_PENDING = 'pending';
   const STATUS_COMPLETED = 'completed';
   const STATUS_FAILED = 'failed';
   const STATUS_REFUNDED = 'refunded';
   const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
   const STATUS_CANCELLED = 'cancelled';

   /**
    * Liste des types possibles
    */
   const TYPE_PAYMENT = 'payment';
   const TYPE_REFUND = 'refund';
   const TYPE_SUBSCRIPTION = 'subscription';

   /**
    * Récupère le client associé à cette transaction
    *
    * @return BelongsTo
    */
   public function customer(): BelongsTo
   {
      return $this->belongsTo(Customer::class, 'customer_id');
   }

   /**
    * Récupère l'abonnement associé à cette transaction
    *
    * @return BelongsTo|null
    */
   public function subscription(): ?BelongsTo
   {
      return $this->subscription_id
         ? $this->belongsTo(Subscription::class, 'subscription_id')
         : null;
   }

   /**
    * Récupère les transactions de remboursement associées à cette transaction
    *
    * @return HasMany
    */
   public function refunds(): HasMany
   {
      return $this->hasMany(self::class, 'reference')
         ->where('type', self::TYPE_REFUND);
   }

   /**
    * Vérifie si la transaction est terminée avec succès
    *
    * @return bool
    */
   public function isSuccessful(): bool
   {
      return $this->status === self::STATUS_COMPLETED;
   }

   /**
    * Vérifie si la transaction est en attente
    *
    * @return bool
    */
   public function isPending(): bool
   {
      return $this->status === self::STATUS_PENDING;
   }

   /**
    * Vérifie si la transaction a échoué
    *
    * @return bool
    */
   public function hasFailed(): bool
   {
      return $this->status === self::STATUS_FAILED;
   }

   /**
    * Vérifie si la transaction est remboursée
    *
    * @return bool
    */
   public function isRefunded(): bool
   {
      return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
   }

   /**
    * Vérifie si la transaction est totalement remboursée
    *
    * @return bool
    */
   public function isFullyRefunded(): bool
   {
      return $this->status === self::STATUS_REFUNDED;
   }

   /**
    * Vérifie si la transaction est partiellement remboursée
    *
    * @return bool
    */
   public function isPartiallyRefunded(): bool
   {
      return $this->status === self::STATUS_PARTIALLY_REFUNDED;
   }

   /**
    * Obtient le montant formaté avec la devise
    *
    * @return string
    */
   public function getFormattedAmount(): string
   {
      $currency = strtoupper($this->currency);
      $amount = number_format($this->amount, 2);

      // Formatage en fonction de la devise
      switch ($currency) {
         case 'EUR':
            return "{$amount} €";
         case 'USD':
            return "\${$amount}";
         case 'GBP':
            return "£{$amount}";
         default:
            return "{$amount} {$currency}";
      }
   }
}
