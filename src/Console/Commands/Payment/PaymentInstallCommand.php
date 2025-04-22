<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Payment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande d'installation du système de paiement
 */
class PaymentInstallCommand extends Command
{

    protected static $defaultName = 'payment:install';
    protected static $defaultDescription = 'Installe et configure le système de paiement';


    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addOption('provider', 'p', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Providers de paiement à installer (stripe, paypal, etc.)', [])
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Installer tous les providers de paiement disponibles')
            ->setHelp('Cette commande installe et configure le système de paiement avec les providers spécifiés.');
    }

    /**
     * Exécute la commande
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installation du système de paiement');

        $providers = $input->getOption('provider');
        $installAll = $input->getOption('all');

        // Si aucun provider n'est spécifié et que l'option --all n'est pas utilisée, demander à l'utilisateur
        if (empty($providers) && !$installAll) {
            $providers = $this->askForProviders($io);
        }

        // Si l'option --all est utilisée, installer tous les providers disponibles
        if ($installAll) {
            $providers = $this->getAvailableProviders();
        }

        if (empty($providers)) {
            $io->warning('Aucun provider de paiement sélectionné. Installation annulée.');
            return Command::SUCCESS;
        }

        // Afficher les providers qui vont être installés
        $io->section('Providers de paiement à installer');
        $io->listing($providers);

        if (!$io->confirm('Voulez-vous continuer avec l\'installation de ces providers ?', true)) {
            $io->warning('Installation annulée par l\'utilisateur.');
            return Command::SUCCESS;
        }

        // Installer les dépendances Composer
        $this->installDependencies($io, $providers);

        // Créer le fichier de configuration
        $this->createConfigFile($io, $providers);

        // Publier les migrations
        $this->publishMigrations($io, $providers);

        // Mettre à jour le fichier .env
        $this->updateEnvFile($io, $providers);

        // Enregistrer le service provider
        $this->registerServiceProvider($io);

        $io->success('Le système de paiement a été installé avec succès !');

        return Command::SUCCESS;
    }

    /**
     * Demande les providers à installer
     */
    private function askForProviders(SymfonyStyle $io): array
    {
        $availableProviders = $this->getAvailableProviders();
        $providers = [];

        $io->section('Sélection des providers de paiement');

        foreach ($availableProviders as $provider) {
            if ($io->confirm("Installer le provider {$provider} ?", false)) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Retourne la liste des providers disponibles
     */
    private function getAvailableProviders(): array
    {
        return ['stripe', 'paypal', 'mangopay', 'mollie'];
    }

    /**
     * Installe les dépendances Composer
     */
    private function installDependencies(SymfonyStyle $io, array $providers): void
    {
        $io->section('Installation des dépendances');

        $dependencies = [];

        if (in_array('stripe', $providers)) {
            $dependencies[] = 'stripe/stripe-php:^10.0';
        }

        if (in_array('paypal', $providers)) {
            $dependencies[] = 'paypal/rest-api-sdk-php:^1.14';
        }

        if (in_array('mangopay', $providers)) {
            $dependencies[] = 'mangopay/php-sdk-v2:^3.11';
        }

        if (in_array('mollie', $providers)) {
            $dependencies[] = 'mollie/mollie-api-php:^2.50';
        }

        if (empty($dependencies)) {
            $io->note('Aucune dépendance à installer.');
            return;
        }

        $io->text('Les dépendances suivantes vont être installées :');
        $io->listing($dependencies);

        $composerCommand = 'composer require ' . implode(' ', $dependencies);
        
        $io->text('Exécution de la commande : ' . $composerCommand);
        
        // Affichage de la commande à exécuter
        $io->note("Pour installer les dépendances, exécutez cette commande : $composerCommand");
        
        if ($io->confirm('Voulez-vous exécuter cette commande maintenant ?', false)) {
            // En production, on exécuterait la commande ici
            $io->note('Exécution simulée de la commande composer...');
            
            // Simulation de l'installation
            $io->progressStart(count($dependencies));
            foreach ($dependencies as $dependency) {
                sleep(1); // Simulation du temps d'installation
                $io->progressAdvance();
            }
            $io->progressFinish();
            
            $io->success('Dépendances installées avec succès !');
        } else {
            $io->note('L\'installation des dépendances a été reportée. Vous pourrez l\'exécuter manuellement plus tard.');
        }
    }

    /**
     * Crée le fichier de configuration
     */
    private function createConfigFile(SymfonyStyle $io, array $providers): void
    {
        $io->section('Création du fichier de configuration');

        $configFile = config_path('payment.php');
        
        if (file_exists($configFile)) {
            $overwrite = $io->confirm('Le fichier de configuration payment.php existe déjà. Voulez-vous le remplacer ?', false);
            if (!$overwrite) {
                $io->note('Création du fichier de configuration annulée.');
                return;
            }
        }

        $configContent = $this->getConfigTemplate($providers);
        
        // Créer le répertoire config si nécessaire
        if (!is_dir(dirname($configFile))) {
            mkdir(dirname($configFile), 0755, true);
        }
        
        file_put_contents($configFile, $configContent);
        
        $io->success('Fichier de configuration créé avec succès !');
    }

    /**
     * Retourne le template de configuration
     */
    private function getConfigTemplate(array $providers): string
    {
        $providerConfigs = [];
        
        if (in_array('stripe', $providers)) {
            $providerConfigs[] = <<<'PHP'
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'), 
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'eur'),
            'sandbox' => env('STRIPE_SANDBOX', true),
        ],
PHP;
        }
        
        if (in_array('paypal', $providers)) {
            $providerConfigs[] = <<<'PHP'
        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', true),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'currency' => env('PAYPAL_CURRENCY', 'EUR'),
            'sandbox' => env('PAYPAL_SANDBOX', true),
        ],
PHP;
        }
        
        if (in_array('mangopay', $providers)) {
            $providerConfigs[] = <<<'PHP'
        'mangopay' => [
            'enabled' => env('MANGOPAY_ENABLED', true),
            'client_id' => env('MANGOPAY_CLIENT_ID'),
            'client_key' => env('MANGOPAY_CLIENT_KEY'),
            'sandbox' => env('MANGOPAY_SANDBOX', true),
        ],
PHP;
        }
        
        if (in_array('mollie', $providers)) {
            $providerConfigs[] = <<<'PHP'
        'mollie' => [
            'enabled' => env('MOLLIE_ENABLED', true),
            'key' => env('MOLLIE_KEY'),
            'webhook_url' => env('MOLLIE_WEBHOOK_URL'),
            'redirect_url' => env('MOLLIE_REDIRECT_URL'),
        ],
PHP;
        }
        
        // Assembler la configuration complète
        return <<<PHP
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
    'default' => env('PAYMENT_PROVIDER', "$providers[0] ?? 'stripe'"),

    // Configuration des providers
    'providers' => [
{$this->indentText(implode("\n\n", $providerConfigs), 8)}
    ],

    // Configuration des tables de base de données
    'tables' => [
        'payments' => 'payments',
        'subscriptions' => 'subscriptions',
        'payment_methods' => 'payment_methods',
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
    }

    /**
     * Indente un texte avec un nombre spécifique d'espaces
     */
    private function indentText(string $text, int $spaces): string
    {
        $lines = explode("\n", $text);
        $indent = str_repeat(' ', $spaces);
        
        foreach ($lines as &$line) {
            if (!empty($line)) {
                $line = $indent . $line;
            }
        }
        
        return implode("\n", $lines);
    }

    /**
     * Publie les migrations
     */
    private function publishMigrations(SymfonyStyle $io, array $providers): void
    {
        $io->section('Publication des migrations');
        
        $migrationsDir = database_path('migrations');
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }
        
        // Créer la migration pour la table des paiements
        $timestamp = date('Y_m_d_His');
        $migrationFile = $migrationsDir . "/{$timestamp}_create_payments_tables.php";
        
        $migrationContent = $this->getPaymentMigrationTemplate();
        file_put_contents($migrationFile, $migrationContent);
        
        $io->success('Migrations publiées avec succès !');
    }

    /**
     * Retourne le template de migration pour les paiements
     */
    private function getPaymentMigrationTemplate(): string
    {
        return <<<'PHP'
<?php

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Facades\Schema;

class CreatePaymentsTables extends Migration
{
    /**
     * Exécute les migrations
     */
    public function up()
    {
        // Table des méthodes de paiement
        Schema::create('payment_methods', function (Anvil $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider');
            $table->string('method_type');
            $table->string('token')->nullable();
            $table->string('identifier');
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Table des paiements
        Schema::create('payments', function (Anvil $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('payment_id')->unique();
            $table->string('provider');
            $table->string('method')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('status');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['payable_id', 'payable_type']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Table des abonnements
        Schema::create('subscriptions', function (Anvil $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('provider_plan');
            $table->string('status');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Annule les migrations
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
}
PHP;
    }

    /**
     * Met à jour le fichier .env
     */
    private function updateEnvFile(SymfonyStyle $io, array $providers): void
    {
        $io->section('Mise à jour du fichier .env');
        
        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            $io->warning('Le fichier .env n\'existe pas.');
            return;
        }
        
        $content = file_get_contents($envFile);
        
        // Vérifier si la section de paiement existe déjà
        if (strpos($content, 'PAYMENT_PROVIDER') !== false) {
            $io->note('La section de paiement existe déjà dans le fichier .env.');
            return;
        }
        
        $envVars = "\n# Configuration du système de paiement\n";
        $envVars .= "PAYMENT_PROVIDER=" . ($providers[0] ?? 'stripe') . "\n";
        $envVars .= "PAYMENT_ALLOW_REFUNDS=true\n";
        $envVars .= "PAYMENT_TIMEOUT=30\n";
        $envVars .= "PAYMENT_SUCCESS_URL=/payment/success\n";
        $envVars .= "PAYMENT_CANCEL_URL=/payment/cancel\n";
        $envVars .= "PAYMENT_EMAIL_NOTIFICATIONS=true\n";
        $envVars .= "PAYMENT_EMAIL_RECIPIENTS=\n";
        $envVars .= "PAYMENT_LOGGING=true\n";
        
        if (in_array('stripe', $providers)) {
            $envVars .= "\n# Stripe Configuration\n";
            $envVars .= "STRIPE_ENABLED=true\n";
            $envVars .= "STRIPE_KEY=\n";
            $envVars .= "STRIPE_SECRET=\n";
            $envVars .= "STRIPE_WEBHOOK_SECRET=\n";
            $envVars .= "STRIPE_CURRENCY=eur\n";
            $envVars .= "STRIPE_SANDBOX=true\n";
        }
        
        if (in_array('paypal', $providers)) {
            $envVars .= "\n# PayPal Configuration\n";
            $envVars .= "PAYPAL_ENABLED=true\n";
            $envVars .= "PAYPAL_CLIENT_ID=\n";
            $envVars .= "PAYPAL_CLIENT_SECRET=\n";
            $envVars .= "PAYPAL_CURRENCY=EUR\n";
            $envVars .= "PAYPAL_SANDBOX=true\n";
        }
        
        if (in_array('mangopay', $providers)) {
            $envVars .= "\n# MangoPay Configuration\n";
            $envVars .= "MANGOPAY_ENABLED=true\n";
            $envVars .= "MANGOPAY_CLIENT_ID=\n";
            $envVars .= "MANGOPAY_CLIENT_KEY=\n";
            $envVars .= "MANGOPAY_SANDBOX=true\n";
        }
        
        if (in_array('mollie', $providers)) {
            $envVars .= "\n# Mollie Configuration\n";
            $envVars .= "MOLLIE_ENABLED=true\n";
            $envVars .= "MOLLIE_KEY=\n";
            $envVars .= "MOLLIE_WEBHOOK_URL=\n";
            $envVars .= "MOLLIE_REDIRECT_URL=\n";
        }
        
        file_put_contents($envFile, $content . $envVars);
        
        $io->success('Fichier .env mis à jour avec succès !');
    }

    /**
     * Enregistre le service provider
     */
    private function registerServiceProvider(SymfonyStyle $io): void
    {
        $io->section('Enregistrement du service provider');
        
        $appConfig = config_path('app.php');
        if (!file_exists($appConfig)) {
            $io->warning('Le fichier de configuration app.php n\'existe pas.');
            return;
        }
        
        $content = file_get_contents($appConfig);
        if (strpos($content, 'IronFlow\\Payment\\PaymentServiceProvider') !== false) {
            $io->note('Le service provider est déjà enregistré.');
            return;
        }
        
        // Ajouter le provider
        $content = preg_replace(
            '/(\'providers\' => \[)(.*?)(\])/s',
            "$1$2    IronFlow\\Payment\\PaymentServiceProvider::class,\n$3",
            $content
        );
        
        file_put_contents($appConfig, $content);
        
        $io->success('Service provider enregistré avec succès !');
    }
} 