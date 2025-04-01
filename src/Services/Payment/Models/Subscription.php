<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\BelongsTo;
use IronFlow\Database\Iron\Relations\HasMany;
use DateTime;

/**
 * Modèle représentant un abonnement
 */
class Subscription extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_subscriptions';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'customer_id',
      'provider',
      'provider_subscription_id',
      'provider_payment_method_id',
      'plan_id',
      'status',
      'quantity',
      'trial_ends_at',
      'current_period_starts_at',
      'current_period_ends_at',
      'ends_at',
      'cancellation_reason',
      'metadata',
   ];

   /**
    * Attributs à convertir en dates
    *
    * @var array
    */
   protected array $dates = [
      'trial_ends_at',
      'current_period_starts_at',
      'current_period_ends_at',
      'ends_at',
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
      'quantity' => 'integer',
      'metadata' => 'json',
   ];

   /**
    * Liste des statuts possibles
    */
   const STATUS_ACTIVE = 'active';
   const STATUS_PAST_DUE = 'past_due';
   const STATUS_CANCELED = 'canceled';
   const STATUS_UNPAID = 'unpaid';
   const STATUS_TRIAL = 'trialing';
   const STATUS_INCOMPLETE = 'incomplete';
   const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';

   /**
    * Récupère le client associé à cet abonnement
    *
    * @return BelongsTo
    */
   public function customer(): BelongsTo
   {
      return $this->belongsTo(Customer::class, 'customer_id');
   }

   /**
    * Récupère le plan associé à cet abonnement
    *
    * @return BelongsTo
    */
   public function plan(): BelongsTo
   {
      return $this->belongsTo(Plan::class, 'plan_id');
   }

   /**
    * Récupère les transactions associées à cet abonnement
    *
    * @return HasMany
    */
   public function transactions(): HasMany
   {
      return $this->hasMany(Transaction::class, 'subscription_id');
   }

   /**
    * Vérifie si l'abonnement est actif
    *
    * @return bool
    */
   public function isActive(): bool
   {
      return $this->status === self::STATUS_ACTIVE;
   }

   /**
    * Vérifie si l'abonnement est en période d'essai
    *
    * @return bool
    */
   public function onTrial(): bool
   {
      return $this->status === self::STATUS_TRIAL &&
         $this->trial_ends_at &&
         $this->trial_ends_at > new DateTime();
   }

   /**
    * Vérifie si l'abonnement est annulé
    *
    * @return bool
    */
   public function isCanceled(): bool
   {
      return $this->status === self::STATUS_CANCELED;
   }

   /**
    * Vérifie si l'abonnement est annulé mais toujours actif jusqu'à la fin de la période en cours
    *
    * @return bool
    */
   public function onGracePeriod(): bool
   {
      return $this->isCanceled() &&
         $this->ends_at &&
         $this->ends_at > new DateTime();
   }

   /**
    * Vérifie si l'abonnement va être renouvelé
    *
    * @return bool
    */
   public function willRenew(): bool
   {
      return $this->isActive() && (!$this->ends_at || $this->ends_at > new DateTime());
   }

   /**
    * Vérifie si l'abonnement est expiré
    *
    * @return bool
    */
   public function hasExpired(): bool
   {
      return $this->ends_at && $this->ends_at <= new DateTime();
   }

   /**
    * Calcule le nombre de jours restants dans la période actuelle
    *
    * @return int|null
    */
   public function daysRemainingInPeriod(): ?int
   {
      if (!$this->current_period_ends_at) {
         return null;
      }

      $now = new DateTime();
      $diff = $now->diff($this->current_period_ends_at);

      return ($diff->invert === 0) ? $diff->days : 0;
   }

   /**
    * Récupère la date de renouvellement formatée
    *
    * @param string $format Format de date (défaut: d/m/Y)
    * @return string|null
    */
   public function getRenewalDate(string $format = 'd/m/Y'): ?string
   {
      if (!$this->current_period_ends_at || !$this->willRenew()) {
         return null;
      }

      return $this->current_period_ends_at->format($format);
   }
}
