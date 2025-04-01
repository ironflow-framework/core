<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment;

use IronFlow\Services\Payment\Contracts\PaymentProviderInterface;
use IronFlow\Services\Payment\Exceptions\PaymentException;
use IronFlow\Services\Payment\Models\Customer;
use IronFlow\Services\Payment\Models\PaymentIntent;
use IronFlow\Services\Payment\Models\PaymentMethod;
use IronFlow\Services\Payment\Models\Plan;
use IronFlow\Services\Payment\Models\Subscription;
use IronFlow\Services\Payment\Models\Transaction;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Log;

/**
 * Gestionnaire principal du système de paiement
 */
class PaymentManager
{
   /**
    * Liste des providers disponibles
    */
   protected array $providers = [];

   /**
    * Provider par défaut
    */
   protected ?PaymentProviderInterface $defaultProvider = null;

   /**
    * Configuration du système de paiement
    */
   protected array $config;

   /**
    * Initialise le gestionnaire de paiement
    */
   public function __construct()
   {
      $this->config = Config::get('payment', []);
   }

   /**
    * Enregistre un provider de paiement
    */
   public function registerProvider(string $name, PaymentProviderInterface $provider): self
   {
      $this->providers[$name] = $provider;

      // Initialiser le provider avec sa configuration
      $providerConfig = $this->config['providers'][$name] ?? [];
      $provider->initialize($providerConfig);

      return $this;
   }

   /**
    * Définit le provider par défaut
    */
   public function setDefaultProvider(string $name): self
   {
      if (!isset($this->providers[$name])) {
         throw new PaymentException("Le provider de paiement '$name' n'est pas enregistré");
      }

      $this->defaultProvider = $this->providers[$name];

      return $this;
   }

   /**
    * Récupère un provider de paiement
    */
   public function provider(?string $name = null): PaymentProviderInterface
   {
      // Si aucun nom n'est fourni, utiliser le provider par défaut
      if ($name === null) {
         if ($this->defaultProvider === null) {
            $defaultName = $this->config['default'] ?? array_key_first($this->providers);

            if (!$defaultName || !isset($this->providers[$defaultName])) {
               throw new PaymentException("Aucun provider de paiement par défaut n'est configuré");
            }

            $this->setDefaultProvider($defaultName);
         }

         return $this->defaultProvider;
      }

      // Sinon, récupérer le provider demandé
      if (!isset($this->providers[$name])) {
         throw new PaymentException("Le provider de paiement '$name' n'est pas enregistré");
      }

      return $this->providers[$name];
   }

   /**
    * Crée un client
    */
   public function createCustomer(array $customerData, ?string $provider = null): Customer
   {
      try {
         return $this->provider($provider)->createCustomer($customerData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création du client', $e, $customerData);
         throw new PaymentException('Impossible de créer le client: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère un client
    */
   public function getCustomer(string $customerId, ?string $provider = null): ?Customer
   {
      try {
         return $this->provider($provider)->getCustomer($customerId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération du client', $e, ['customer_id' => $customerId]);
         throw new PaymentException('Impossible de récupérer le client: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Met à jour un client
    */
   public function updateCustomer(string $customerId, array $customerData, ?string $provider = null): Customer
   {
      try {
         return $this->provider($provider)->updateCustomer($customerId, $customerData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la mise à jour du client', $e, ['customer_id' => $customerId, 'data' => $customerData]);
         throw new PaymentException('Impossible de mettre à jour le client: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Supprime un client
    */
   public function deleteCustomer(string $customerId, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->deleteCustomer($customerId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la suppression du client', $e, ['customer_id' => $customerId]);
         throw new PaymentException('Impossible de supprimer le client: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Crée une intention de paiement
    */
   public function createPaymentIntent(array $intentData, ?string $provider = null): PaymentIntent
   {
      try {
         return $this->provider($provider)->createPaymentIntent($intentData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création de l\'intention de paiement', $e, $intentData);
         throw new PaymentException('Impossible de créer l\'intention de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère une intention de paiement
    */
   public function getPaymentIntent(string $intentId, ?string $provider = null): ?PaymentIntent
   {
      try {
         return $this->provider($provider)->getPaymentIntent($intentId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération de l\'intention de paiement', $e, ['intent_id' => $intentId]);
         throw new PaymentException('Impossible de récupérer l\'intention de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Confirme une intention de paiement
    */
   public function confirmPaymentIntent(string $intentId, array $options = [], ?string $provider = null): PaymentIntent
   {
      try {
         return $this->provider($provider)->confirmPaymentIntent($intentId, $options);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la confirmation de l\'intention de paiement', $e, ['intent_id' => $intentId, 'options' => $options]);
         throw new PaymentException('Impossible de confirmer l\'intention de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Annule une intention de paiement
    */
   public function cancelPaymentIntent(string $intentId, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->cancelPaymentIntent($intentId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de l\'annulation de l\'intention de paiement', $e, ['intent_id' => $intentId]);
         throw new PaymentException('Impossible d\'annuler l\'intention de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Crée une transaction
    */
   public function createTransaction(array $transactionData, ?string $provider = null): Transaction
   {
      try {
         return $this->provider($provider)->createTransaction($transactionData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création de la transaction', $e, $transactionData);
         throw new PaymentException('Impossible de créer la transaction: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère une transaction
    */
   public function getTransaction(string $transactionId, ?string $provider = null): ?Transaction
   {
      try {
         return $this->provider($provider)->getTransaction($transactionId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération de la transaction', $e, ['transaction_id' => $transactionId]);
         throw new PaymentException('Impossible de récupérer la transaction: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Confirme une transaction
    */
   public function confirmTransaction(string $transactionId, ?string $provider = null): Transaction
   {
      try {
         return $this->provider($provider)->confirmTransaction($transactionId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la confirmation de la transaction', $e, ['transaction_id' => $transactionId]);
         throw new PaymentException('Impossible de confirmer la transaction: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Annule une transaction
    */
   public function cancelTransaction(string $transactionId, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->cancelTransaction($transactionId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de l\'annulation de la transaction', $e, ['transaction_id' => $transactionId]);
         throw new PaymentException('Impossible d\'annuler la transaction: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Rembourse une transaction
    */
   public function refundTransaction(string $transactionId, ?float $amount = null, ?string $provider = null): Transaction
   {
      if (!($this->config['allow_refunds'] ?? true)) {
         throw new PaymentException('Les remboursements ne sont pas autorisés dans la configuration');
      }

      try {
         return $this->provider($provider)->refundTransaction($transactionId, $amount);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors du remboursement de la transaction', $e, ['transaction_id' => $transactionId, 'amount' => $amount]);
         throw new PaymentException('Impossible de rembourser la transaction: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Crée une méthode de paiement
    */
   public function createPaymentMethod(string $customerId, array $paymentMethodData, ?string $provider = null): PaymentMethod
   {
      try {
         return $this->provider($provider)->createPaymentMethod($customerId, $paymentMethodData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création de la méthode de paiement', $e, ['customer_id' => $customerId, 'data' => $paymentMethodData]);
         throw new PaymentException('Impossible de créer la méthode de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère les méthodes de paiement d'un client
    */
   public function getPaymentMethods(string $customerId, ?string $provider = null): array
   {
      try {
         return $this->provider($provider)->getPaymentMethods($customerId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération des méthodes de paiement', $e, ['customer_id' => $customerId]);
         throw new PaymentException('Impossible de récupérer les méthodes de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Supprime une méthode de paiement
    */
   public function deletePaymentMethod(string $paymentMethodId, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->deletePaymentMethod($paymentMethodId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la suppression de la méthode de paiement', $e, ['payment_method_id' => $paymentMethodId]);
         throw new PaymentException('Impossible de supprimer la méthode de paiement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Crée un plan d'abonnement
    */
   public function createPlan(array $planData, ?string $provider = null): Plan
   {
      try {
         return $this->provider($provider)->createPlan($planData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création du plan', $e, $planData);
         throw new PaymentException('Impossible de créer le plan: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère un plan d'abonnement
    */
   public function getPlan(string $planId, ?string $provider = null): ?Plan
   {
      try {
         return $this->provider($provider)->getPlan($planId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération du plan', $e, ['plan_id' => $planId]);
         throw new PaymentException('Impossible de récupérer le plan: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Met à jour un plan d'abonnement
    */
   public function updatePlan(string $planId, array $planData, ?string $provider = null): Plan
   {
      try {
         return $this->provider($provider)->updatePlan($planId, $planData);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la mise à jour du plan', $e, ['plan_id' => $planId, 'data' => $planData]);
         throw new PaymentException('Impossible de mettre à jour le plan: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Supprime un plan d'abonnement
    */
   public function deletePlan(string $planId, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->deletePlan($planId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la suppression du plan', $e, ['plan_id' => $planId]);
         throw new PaymentException('Impossible de supprimer le plan: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Crée un abonnement
    */
   public function createSubscription(string $customerId, string $planId, array $options = [], ?string $provider = null): Subscription
   {
      try {
         return $this->provider($provider)->createSubscription($customerId, $planId, $options);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la création de l\'abonnement', $e, ['customer_id' => $customerId, 'plan_id' => $planId, 'options' => $options]);
         throw new PaymentException('Impossible de créer l\'abonnement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Récupère un abonnement
    */
   public function getSubscription(string $subscriptionId, ?string $provider = null): ?Subscription
   {
      try {
         return $this->provider($provider)->getSubscription($subscriptionId);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la récupération de l\'abonnement', $e, ['subscription_id' => $subscriptionId]);
         throw new PaymentException('Impossible de récupérer l\'abonnement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Met à jour un abonnement
    */
   public function updateSubscription(string $subscriptionId, array $data, ?string $provider = null): Subscription
   {
      try {
         return $this->provider($provider)->updateSubscription($subscriptionId, $data);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la mise à jour de l\'abonnement', $e, ['subscription_id' => $subscriptionId, 'data' => $data]);
         throw new PaymentException('Impossible de mettre à jour l\'abonnement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Annule un abonnement
    */
   public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->cancelSubscription($subscriptionId, $atPeriodEnd);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de l\'annulation de l\'abonnement', $e, ['subscription_id' => $subscriptionId, 'at_period_end' => $atPeriodEnd]);
         throw new PaymentException('Impossible d\'annuler l\'abonnement: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Gère un webhook entrant
    */
   public function handleWebhook(string $payload, array $headers, ?string $provider = null): array
   {
      try {
         return $this->provider($provider)->handleWebhook($payload, $headers);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors du traitement du webhook', $e, ['headers' => $headers]);
         throw new PaymentException('Impossible de traiter le webhook: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Vérifie la signature d'un webhook
    */
   public function verifyWebhookSignature(string $payload, string $signature, string $secret, ?string $provider = null): bool
   {
      try {
         return $this->provider($provider)->verifyWebhookSignature($payload, $signature, $secret);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la vérification de la signature du webhook', $e, []);
         throw new PaymentException('Impossible de vérifier la signature du webhook: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Génère un token de paiement côté client
    */
   public function generateClientToken(array $options = [], ?string $provider = null): string
   {
      try {
         return $this->provider($provider)->generateClientToken($options);
      } catch (\Throwable $e) {
         $this->logError('Erreur lors de la génération du token client', $e, $options);
         throw new PaymentException('Impossible de générer le token client: ' . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Journalise une erreur
    */
   protected function logError(string $message, \Throwable $exception, array $context = []): void
   {
      if ($this->config['logging']['enabled'] ?? true) {
         $channel = $this->config['logging']['channel'] ?? 'stack';
         Log::channel($channel)->error($message, array_merge([
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
         ], $context));
      }
   }
}
