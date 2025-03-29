<?php

declare(strict_types=1);

namespace IronFlow\Payment\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Relations\BelongsTo;

/**
 * Modèle représentant une méthode de paiement
 */
class PaymentMethod extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected string $table = 'payment_methods';

   /**
    * Attributs assignables en masse
    *
    * @var array
    */
   protected array $fillable = [
      'customer_id',
      'provider',
      'provider_payment_method_id',
      'type',
      'card_brand',
      'card_last_four',
      'card_expiry_month',
      'card_expiry_year',
      'billing_address',
      'billing_city',
      'billing_state',
      'billing_country',
      'billing_postal_code',
      'is_default',
      'metadata',
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
      'card_expiry_month' => 'integer',
      'card_expiry_year' => 'integer',
      'is_default' => 'boolean',
      'metadata' => 'json',
   ];

   /**
    * Récupère le client associé à cette méthode de paiement
    *
    * @return BelongsTo
    */
   public function customer(): BelongsTo
   {
      return $this->belongsTo(Customer::class, 'customer_id');
   }

   /**
    * Définit cette méthode de paiement comme la méthode par défaut pour le client
    *
    * @return bool
    */
   public function setAsDefault(): bool
   {
      // Mettre à jour toutes les autres méthodes de paiement du client
      self::where('customer_id', $this->customer_id)
         ->update(['is_default' => false]);

      // Mettre à jour cette méthode de paiement
      $this->is_default = true;
      $result = $this->save();

      // Mettre à jour la référence dans le client
      if ($result) {
         $customer = $this->customer()->first();
         if ($customer) {
            $customer->default_payment_method_id = $this->id;
            $customer->save();
         }
      }

      return $result;
   }

   /**
    * Retourne le nom affiché de la carte (ex: Visa se terminant par 4242)
    *
    * @return string
    */
   public function getDisplayName(): string
   {
      if ($this->type === 'card' && $this->card_brand && $this->card_last_four) {
         return "{$this->card_brand} se terminant par {$this->card_last_four}";
      }

      return $this->type;
   }

   /**
    * Vérifie si la carte est expirée
    *
    * @return bool
    */
   public function isExpired(): bool
   {
      if ($this->type !== 'card' || !$this->card_expiry_month || !$this->card_expiry_year) {
         return false;
      }

      $now = new \DateTime();
      $expiryDate = new \DateTime();
      $expiryDate->setDate($this->card_expiry_year, $this->card_expiry_month, 1);
      $expiryDate->modify('last day of this month');

      return $now > $expiryDate;
   }

   /**
    * Récupère la date d'expiration formatée (MM/YY)
    *
    * @return string|null
    */
   public function getExpiryDateFormatted(): ?string
   {
      if ($this->type !== 'card' || !$this->card_expiry_month || !$this->card_expiry_year) {
         return null;
      }

      $month = str_pad((string) $this->card_expiry_month, 2, '0', STR_PAD_LEFT);
      $year = substr((string) $this->card_expiry_year, -2);

      return "{$month}/{$year}";
   }
}
