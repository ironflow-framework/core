<?php

declare(strict_types=1);

namespace IronFlow\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Relations\HasMany;

/**
 * Modèle représentant un plan d'abonnement
 */
class Plan extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_plans';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'provider',
      'provider_plan_id',
      'name',
      'description',
      'amount',
      'currency',
      'interval',
      'interval_count',
      'trial_period_days',
      'active',
      'metadata',
      'features',
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
      'amount' => 'float',
      'interval_count' => 'integer',
      'trial_period_days' => 'integer',
      'active' => 'boolean',
      'metadata' => 'json',
      'features' => 'json',
   ];

   /**
    * Récupère les abonnements associés à ce plan
    *
    * @return HasMany
    */
   public function subscriptions(): HasMany
   {
      return $this->hasMany(Subscription::class, 'plan_id');
   }

   /**
    * Récupère le montant formaté
    *
    * @return string
    */
   public function getFormattedAmount(): string
   {
      $currencySymbols = [
         'eur' => '€',
         'usd' => '$',
         'gbp' => '£',
      ];

      $symbol = $currencySymbols[strtolower($this->currency)] ?? $this->currency;

      return number_format($this->amount, 2) . ' ' . $symbol;
   }

   /**
    * Récupère la période formatée
    *
    * @return string
    */
   public function getFormattedPeriod(): string
   {
      $intervals = [
         'day' => 'jour',
         'week' => 'semaine',
         'month' => 'mois',
         'year' => 'an',
      ];

      $interval = $intervals[$this->interval] ?? $this->interval;

      if ($this->interval_count > 1) {
         // Pluralisation en français
         if ($this->interval === 'year') {
            return $this->interval_count . ' ans';
         }

         return $this->interval_count . ' ' . $interval . 's';
      }

      return '1 ' . $interval;
   }

   /**
    * Récupère la description de la période d'essai
    *
    * @return string|null
    */
   public function getTrialDescription(): ?string
   {
      if (!$this->trial_period_days) {
         return null;
      }

      if ($this->trial_period_days === 1) {
         return '1 jour d\'essai';
      }

      return $this->trial_period_days . ' jours d\'essai';
   }
}
