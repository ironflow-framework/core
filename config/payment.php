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