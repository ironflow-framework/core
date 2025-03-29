<?php

declare(strict_types=1);

namespace IronFlow\Payment;

use IronFlow\Foundation\ServiceProvider;
use IronFlow\Payment\Providers\StripeProvider;
use IronFlow\Payment\Providers\PayPalProvider;
use IronFlow\Payment\Providers\MollieProvider;
use IronFlow\Payment\Http\Controllers\WebhookController;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Route;

/**
 * Fournisseur de services pour le système de paiement
 */
class PaymentServiceProvider extends ServiceProvider
{
   /**
    * Liste des providers disponibles
    */
   protected array $providers = [
      'stripe',
      'paypal',
      'mollie'
   ];

   /**
    * Enregistre les services
    */
   public function register(): void
   {
      $this->app->singleton('payment', function ($app) {
         return new PaymentManager();
      });

      $this->registerProviders();
   }

   /**
    * Démarre les services
    */
   public function boot(): void
   {
      // Enregistrer les providers dans le manager après résolution
      $paymentManager = $this->app->make('payment');
      $this->registerPaymentProviders($paymentManager);

      // $this->registerRoutes();
      $this->publishConfig();
      $this->publishMigrations();

      if (!file_exists(config_path('payment.php'))) {
         $this->createDefaultConfig();
      }
   }

   /**
    * Enregistre les providers de paiement dans le container
    */
   protected function registerProviders(): void
   {
      $this->app->singleton('payment.stripe', function ($app) {
         return new StripeProvider();
      });

      $this->app->singleton('payment.paypal', function ($app) {
         return new PayPalProvider();
      });

      $this->app->singleton('payment.mollie', function ($app) {
         return new MollieProvider();
      });
   }

   /**
    * Enregistre les providers de paiement dans le manager
    */
   protected function registerPaymentProviders(PaymentManager $manager): void
   {
      $manager->registerProvider('stripe', $this->app->make('payment.stripe'));
      $manager->registerProvider('paypal', $this->app->make('payment.paypal'));
      $manager->registerProvider('mollie', $this->app->make('payment.mollie'));

      // Définir le provider par défaut
      $defaultProvider = Config::get('payment.default', 'stripe');
      if (in_array($defaultProvider, $this->providers)) {
         $manager->setDefaultProvider($defaultProvider);
      }
   }

   /**
    * Enregistre les routes pour les webhooks (à implémenter ultérieurement)
    */
   /*
   protected function registerRoutes(): void
   {
      if (class_exists('IronFlow\Support\Facades\Route')) {
         Route::group(['prefix' => 'payment', 'middleware' => 'web'], function () {
            Route::post('/webhook/{provider}', [WebhookController::class, 'handleWebhook'])->name('payment.webhook');
            Route::get('/success', [WebhookController::class, 'success'])->name('payment.success');
            Route::get('/cancel', [WebhookController::class, 'cancel'])->name('payment.cancel');
         });
      }
   }
   */

   /**
    * Publie les fichiers de configuration
    */
   protected function publishConfig(): void
   {
      $this->publishes([
         __DIR__ . '/../../config/payment.php' => config_path('payment.php'),
      ], 'payment-config');
   }

   /**
    * Publie les migrations
    */
   protected function publishMigrations(): void
   {
      $this->publishes([
         __DIR__ . '/../../database/migrations/payment' => database_path('migrations'),
      ], 'payment-migrations');
   }

   /**
    * Crée une configuration par défaut
    */
   protected function createDefaultConfig(): void
   {
      if (!is_dir(dirname(config_path('payment.php')))) {
         mkdir(dirname(config_path('payment.php')), 0755, true);
      }

      $configContent = <<<'PHP'
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de paiement
    |--------------------------------------------------------------------------
    |
    | Cette configuration définit les paramètres du système de paiement d'IronFlow,
    | notamment le provider par défaut et les options des différents providers.
    |
    */

    // Provider par défaut
    'default' => env('PAYMENT_PROVIDER', 'stripe'),

    // Configuration des providers
    'providers' => [
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'eur'),
            'sandbox' => env('STRIPE_SANDBOX', true),
        ],
        
        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', true),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'currency' => env('PAYPAL_CURRENCY', 'EUR'),
            'sandbox' => env('PAYPAL_SANDBOX', true),
        ],
        
        'mollie' => [
            'enabled' => env('MOLLIE_ENABLED', true),
            'key' => env('MOLLIE_KEY'),
            'webhook_url' => env('MOLLIE_WEBHOOK_URL'),
            'redirect_url' => env('MOLLIE_REDIRECT_URL'),
        ],
    ],

    // Configuration des tables de base de données
    'tables' => [
        'customers' => 'payment_customers',
        'payment_methods' => 'payment_methods',
        'payment_intents' => 'payment_intents',
        'transactions' => 'payment_transactions',
        'plans' => 'payment_plans',
        'subscriptions' => 'payment_subscriptions',
    ],

    // Autorise les remboursements
    'allow_refunds' => env('PAYMENT_ALLOW_REFUNDS', true),
    
    // Délai d'expiration des paiements (en minutes)
    'payment_timeout' => env('PAYMENT_TIMEOUT', 30),
    
    // URL de redirection après paiement
    'success_url' => env('PAYMENT_SUCCESS_URL', '/payment/success'),
    'cancel_url' => env('PAYMENT_CANCEL_URL', '/payment/cancel'),
    
    // Notification par email lors d'un paiement
    'email_notifications' => [
        'enabled' => env('PAYMENT_EMAIL_NOTIFICATIONS', true),
        'recipients' => explode(',', env('PAYMENT_EMAIL_RECIPIENTS', '')),
    ],
    
    // Journalisation des paiements
    'logging' => [
        'enabled' => env('PAYMENT_LOGGING', true),
        'channel' => env('PAYMENT_LOG_CHANNEL', 'stack'),
    ],
];
PHP;

      file_put_contents(config_path('payment.php'), $configContent);
   }
}
