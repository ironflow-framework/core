<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Facades;

use IronFlow\Services\Payment\Models\Customer;
use IronFlow\Services\Payment\Models\PaymentIntent;
use IronFlow\Services\Payment\Models\PaymentMethod;
use IronFlow\Services\Payment\Models\Plan;
use IronFlow\Services\Payment\Models\Subscription;
use IronFlow\Services\Payment\Models\Transaction;

/**
 * Façade pour le système de paiement
 * 
 * @method static \IronFlow\Payment\Contracts\PaymentProviderInterface provider(string $name = null)
 * @method static \IronFlow\Payment\PaymentManager registerProvider(string $name, \IronFlow\Payment\Contracts\PaymentProviderInterface $provider)
 * @method static \IronFlow\Payment\PaymentManager setDefaultProvider(string $name)
 * @method static Customer createCustomer(array $customerData, string $provider = null)
 * @method static Customer|null getCustomer(string $customerId, string $provider = null)
 * @method static Customer updateCustomer(string $customerId, array $customerData, string $provider = null)
 * @method static bool deleteCustomer(string $customerId, string $provider = null)
 * @method static PaymentIntent createPaymentIntent(array $intentData, string $provider = null)
 * @method static PaymentIntent|null getPaymentIntent(string $intentId, string $provider = null)
 * @method static PaymentIntent confirmPaymentIntent(string $intentId, array $options = [], string $provider = null)
 * @method static bool cancelPaymentIntent(string $intentId, string $provider = null)
 * @method static Transaction createTransaction(array $transactionData, string $provider = null)
 * @method static Transaction|null getTransaction(string $transactionId, string $provider = null)
 * @method static Transaction confirmTransaction(string $transactionId, string $provider = null)
 * @method static bool cancelTransaction(string $transactionId, string $provider = null)
 * @method static Transaction refundTransaction(string $transactionId, float|null $amount = null, string $provider = null)
 * @method static PaymentMethod createPaymentMethod(string $customerId, array $paymentMethodData, string $provider = null)
 * @method static array getPaymentMethods(string $customerId, string $provider = null)
 * @method static bool deletePaymentMethod(string $paymentMethodId, string $provider = null)
 * @method static Plan createPlan(array $planData, string $provider = null)
 * @method static Plan|null getPlan(string $planId, string $provider = null)
 * @method static Plan updatePlan(string $planId, array $planData, string $provider = null)
 * @method static bool deletePlan(string $planId, string $provider = null)
 * @method static Subscription createSubscription(string $customerId, string $planId, array $options = [], string $provider = null)
 * @method static Subscription|null getSubscription(string $subscriptionId, string $provider = null)
 * @method static Subscription updateSubscription(string $subscriptionId, array $data, string $provider = null)
 * @method static bool cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true, string $provider = null)
 * @method static array handleWebhook(string $payload, array $headers, string $provider = null)
 * @method static bool verifyWebhookSignature(string $payload, string $signature, string $secret, string $provider = null)
 * @method static string generateClientToken(array $options = [], string $provider = null)
 * 
 * @see \IronFlow\Services\Payment\PaymentManager
 */
class Payment
{
   /**
    * Récupère le nom d'enregistrement du composant
    */
   protected static function getFacadeAccessor(): string
   {
      return 'payment';
   }

   public static function __callStatic($method, $args)
   {
      return static::getFacadeRoot()->$method(...$args);
   }
}
