<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Contracts;

use IronFlow\Services\Payment\Models\Customer;
use IronFlow\Services\Payment\Models\PaymentIntent;
use IronFlow\Services\Payment\Models\PaymentMethod;
use IronFlow\Services\Payment\Models\Subscription;
use IronFlow\Services\Payment\Models\Transaction;
use IronFlow\Services\Payment\Models\Plan;

/**
 * Interface principale pour les fournisseurs de paiement
 */
interface PaymentProviderInterface
{
   /**
    * Initialise le provider avec les informations d'API
    *
    * @param array $config Configuration du provider
    * @return self
    */
   public function initialize(array $config): self;

   /**
    * Vérifie si le provider est correctement configuré
    *
    * @return bool
    */
   public function isConfigured(): bool;

   /**
    * Crée un client chez le fournisseur de paiement
    *
    * @param array $customerData Données du client
    * @return Customer
    */
   public function createCustomer(array $customerData): Customer;

   /**
    * Récupère un client depuis le fournisseur de paiement
    *
    * @param string $customerId Identifiant du client
    * @return Customer|null
    */
   public function getCustomer(string $customerId): ?Customer;

   /**
    * Met à jour un client chez le fournisseur de paiement
    *
    * @param string $customerId Identifiant du client
    * @param array $customerData Nouvelles données du client
    * @return Customer
    */
   public function updateCustomer(string $customerId, array $customerData): Customer;

   /**
    * Supprime un client chez le fournisseur de paiement
    *
    * @param string $customerId Identifiant du client
    * @return bool
    */
   public function deleteCustomer(string $customerId): bool;

   /**
    * Crée une intention de paiement
    * 
    * @param array $intentData Données de l'intention de paiement
    * @return PaymentIntent
    */
   public function createPaymentIntent(array $intentData): PaymentIntent;

   /**
    * Récupère une intention de paiement
    * 
    * @param string $intentId Identifiant de l'intention de paiement
    * @return PaymentIntent|null
    */
   public function getPaymentIntent(string $intentId): ?PaymentIntent;

   /**
    * Confirme une intention de paiement
    * 
    * @param string $intentId Identifiant de l'intention de paiement
    * @param array $options Options de confirmation
    * @return PaymentIntent
    */
   public function confirmPaymentIntent(string $intentId, array $options = []): PaymentIntent;

   /**
    * Annule une intention de paiement
    * 
    * @param string $intentId Identifiant de l'intention de paiement
    * @return bool
    */
   public function cancelPaymentIntent(string $intentId): bool;

   /**
    * Crée une transaction de paiement
    *
    * @param array $transactionData Données de la transaction
    * @return Transaction
    */
   public function createTransaction(array $transactionData): Transaction;

   /**
    * Récupère une transaction de paiement
    * 
    * @param string $transactionId Identifiant de la transaction
    * @return Transaction|null
    */
   public function getTransaction(string $transactionId): ?Transaction;

   /**
    * Confirme une transaction de paiement
    *
    * @param string $transactionId Identifiant de la transaction
    * @return Transaction
    */
   public function confirmTransaction(string $transactionId): Transaction;

   /**
    * Annule une transaction de paiement
    *
    * @param string $transactionId Identifiant de la transaction
    * @return bool
    */
   public function cancelTransaction(string $transactionId): bool;

   /**
    * Rembourse une transaction de paiement
    *
    * @param string $transactionId Identifiant de la transaction
    * @param float|null $amount Montant à rembourser (null pour rembourser la totalité)
    * @return Transaction
    */
   public function refundTransaction(string $transactionId, ?float $amount = null): Transaction;

   /**
    * Crée une méthode de paiement pour un client
    *
    * @param string $customerId Identifiant du client
    * @param array $paymentMethodData Données de la méthode de paiement
    * @return PaymentMethod
    */
   public function createPaymentMethod(string $customerId, array $paymentMethodData): PaymentMethod;

   /**
    * Récupère les méthodes de paiement d'un client
    *
    * @param string $customerId Identifiant du client
    * @return array
    */
   public function getPaymentMethods(string $customerId): array;

   /**
    * Supprime une méthode de paiement
    *
    * @param string $paymentMethodId Identifiant de la méthode de paiement
    * @return bool
    */
   public function deletePaymentMethod(string $paymentMethodId): bool;

   /**
    * Crée un plan d'abonnement
    * 
    * @param array $planData Données du plan
    * @return Plan
    */
   public function createPlan(array $planData): Plan;

   /**
    * Récupère un plan d'abonnement
    * 
    * @param string $planId Identifiant du plan
    * @return Plan|null
    */
   public function getPlan(string $planId): ?Plan;

   /**
    * Met à jour un plan d'abonnement
    * 
    * @param string $planId Identifiant du plan
    * @param array $planData Données du plan
    * @return Plan
    */
   public function updatePlan(string $planId, array $planData): Plan;

   /**
    * Supprime un plan d'abonnement
    * 
    * @param string $planId Identifiant du plan
    * @return bool
    */
   public function deletePlan(string $planId): bool;

   /**
    * Crée un abonnement pour un client
    *
    * @param string $customerId Identifiant du client
    * @param string $planId Identifiant du plan
    * @param array $options Options supplémentaires
    * @return Subscription
    */
   public function createSubscription(string $customerId, string $planId, array $options = []): Subscription;

   /**
    * Récupère un abonnement
    *
    * @param string $subscriptionId Identifiant de l'abonnement
    * @return Subscription|null
    */
   public function getSubscription(string $subscriptionId): ?Subscription;

   /**
    * Met à jour un abonnement
    *
    * @param string $subscriptionId Identifiant de l'abonnement
    * @param array $data Données à mettre à jour
    * @return Subscription
    */
   public function updateSubscription(string $subscriptionId, array $data): Subscription;

   /**
    * Annule un abonnement
    *
    * @param string $subscriptionId Identifiant de l'abonnement
    * @param bool $atPeriodEnd Annuler à la fin de la période de facturation
    * @return bool
    */
   public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): bool;

   /**
    * Gère un webhook entrant
    *
    * @param string $payload Contenu brut du webhook
    * @param array $headers En-têtes de la requête
    * @return array Données traitées du webhook
    */
   public function handleWebhook(string $payload, array $headers): array;

   /**
    * Vérifie la signature d'un webhook
    * 
    * @param string $payload Contenu brut du webhook
    * @param string $signature Signature du webhook
    * @param string $secret Secret utilisé pour vérifier la signature
    * @return bool
    */
   public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool;

   /**
    * Génère un token de paiement côté client
    * 
    * @param array $options Options pour la génération du token
    * @return string
    */
   public function generateClientToken(array $options = []): string;
}
