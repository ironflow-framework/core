<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Facades\Filesystem;

class SetupCommand extends Command
{
   protected static $defaultName = 'setup';
   protected static $defaultDescription = 'Configuration interactive du framework IronFlow';

   protected function configure(): void
   {
      $this->setHelp('Cette commande vous permet de configurer votre projet IronFlow interactivement');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $io->title('Configuration interactive de IronFlow');

      // Configuration du projet
      $appName = $this->askAppName($io);
      $appType = $this->askAppType($io);
      $dbConfig = $this->askDatabaseConfig($io);
      $authSystem = $this->askAuthSystem($io);
      $cacheSystem = $this->askCacheSystem($io);
      $useCraftPanel = $this->askCraftPanelUsage($io);
      $additionalOptions = $this->askAdditionalOptions($io);

      // Résumé de la configuration
      $this->displayConfigSummary($io, $appName, $appType, $dbConfig, $authSystem, $cacheSystem, $useCraftPanel, $additionalOptions);

      // Confirmation avant de procéder
      if (!$io->confirm('Voulez-vous procéder avec cette configuration ?', true)) {
         $io->warning('Configuration annulée.');
         return Command::FAILURE;
      }

      // Application de la configuration
      $this->applyConfiguration($io, $appName, $appType, $dbConfig, $authSystem, $cacheSystem, $useCraftPanel, $additionalOptions, $output);

      $io->success('Votre projet IronFlow a été configuré avec succès !');
      $io->text([
         'Prochaines étapes:',
         '1. composer install',
         '2. php forge migrate',
         '3. php forge serve'
      ]);

      return Command::SUCCESS;
   }

   private function askAppName(SymfonyStyle $io): string
   {
      return $io->ask('Quel est le nom de votre application ?', 'IronFlow App');
   }

   private function askAppType(SymfonyStyle $io): string
   {
      return $io->choice('Quel type d\'application souhaitez-vous créer ?', [
         'web' => 'Application Web (avec vues)',
         'api' => 'API (sans vues)',
         'full' => 'Application complète (web + API)'
      ], 'web');
   }

   private function askDatabaseConfig(SymfonyStyle $io): array
   {
      $io->section('Configuration de la base de données');

      $driver = $io->choice('Quel pilote de base de données souhaitez-vous utiliser ?', [
         'mysql' => 'MySQL',
         'pgsql' => 'PostgreSQL',
         'sqlite' => 'SQLite',
         'none' => 'Aucun (désactiver la base de données)'
      ], 'mysql');

      if ($driver === 'none') {
         return ['driver' => 'none'];
      }

      $config = ['driver' => $driver];

      if ($driver === 'sqlite') {
         $config['database'] = $io->ask('Nom du fichier de base de données', 'database.sqlite');
      } else {
         $config['host'] = $io->ask('Hôte de la base de données', 'localhost');
         $config['port'] = $io->ask('Port de la base de données', $driver === 'mysql' ? '3306' : '5432');
         $config['database'] = $io->ask('Nom de la base de données', strtolower(str_replace(' ', '_', $config['name'] ?? 'ironflow')));
         $config['username'] = $io->ask('Nom d\'utilisateur', 'root');
         $config['password'] = $io->askHidden('Mot de passe', function ($value) {
            return $value;
         });
      }

      return $config;
   }

   private function askAuthSystem(SymfonyStyle $io): array
   {
      $io->section('Système d\'authentification');

      $useAuth = $io->confirm('Voulez-vous utiliser le système d\'authentification ?', true);

      if (!$useAuth) {
         return ['enabled' => false];
      }

      $driver = $io->choice('Quel type d\'authentification souhaitez-vous utiliser ?', [
         'session' => 'Session (pour applications web)',
         'token' => 'Token JWT (pour API)',
         'guard' => 'Guard (système avancé)',
         'oauth' => 'OAuth (authentification sociale)'
      ], 'session');

      $config = [
         'enabled' => true,
         'driver' => $driver
      ];

      if ($driver === 'oauth') {
         $providers = [];
         foreach (['Google', 'GitHub', 'Facebook', 'Twitter'] as $provider) {
            if ($io->confirm("Activer l'authentification via {$provider} ?", false)) {
               $providers[] = strtolower($provider);
            }
         }
         $config['providers'] = $providers;
      }

      return $config;
   }

   private function askCacheSystem(SymfonyStyle $io): string
   {
      return $io->choice('Quel système de cache souhaitez-vous utiliser ?', [
         'file' => 'Système de fichiers',
         'redis' => 'Redis',
         'memcached' => 'Memcached',
         'array' => 'Array (aucun cache persistant)'
      ], 'file');
   }

   private function askCraftPanelUsage(SymfonyStyle $io): bool
   {
      return $io->confirm('Voulez-vous activer le CraftPanel (panneau d\'administration) ?', true);
   }

   private function askAdditionalOptions(SymfonyStyle $io): array
   {
      $io->section('Options supplémentaires');

      $options = [];

      $options['i18n'] = $io->confirm('Activer l\'internationalisation ?', true);

      if ($options['i18n']) {
         $options['default_locale'] = $io->choice('Langue par défaut', [
            'fr' => 'Français',
            'en' => 'Anglais',
            'es' => 'Espagnol',
            'de' => 'Allemand'
         ], 'fr');
      }

      $options['logger'] = $io->confirm('Activer le système de journalisation avancé ?', true);
      $options['mail'] = $io->confirm('Configurer le service de mail ?', true);
      $options['queue'] = $io->confirm('Activer le système de file d\'attente ?', false);
      $options['payment'] = $io->confirm('Intégrer un système de paiement ?', false);

      if ($options['payment']) {
         $options['payment_providers'] = [];
         foreach (['Stripe', 'PayPal', 'MangoPay'] as $provider) {
            if ($io->confirm("Intégrer {$provider} ?", $provider === 'Stripe')) {
               $options['payment_providers'][] = strtolower($provider);
            }
         }
      }

      $options['ai'] = $io->confirm('Intégrer des services d\'IA ?', false);

      if ($options['ai']) {
         $options['ai_providers'] = [];
         foreach (['ChatGPT', 'Claude', 'Gemini'] as $provider) {
            if ($io->confirm("Intégrer {$provider} ?", $provider === 'ChatGPT')) {
               $options['ai_providers'][] = strtolower($provider);
            }
         }
      }

      return $options;
   }

   private function displayConfigSummary(
      SymfonyStyle $io,
      string $appName,
      string $appType,
      array $dbConfig,
      array $authSystem,
      string $cacheSystem,
      bool $useCraftPanel,
      array $additionalOptions
   ): void {
      $io->section('Résumé de la configuration');

      $io->definitionList(
         ['Nom de l\'application' => $appName],
         ['Type d\'application' => $appType],
         ['Base de données' => $dbConfig['driver'] === 'none' ? 'Désactivée' : $dbConfig['driver']],
         ['Authentification' => $authSystem['enabled'] ? $authSystem['driver'] : 'Désactivée'],
         ['Système de cache' => $cacheSystem],
         ['CraftPanel' => $useCraftPanel ? 'Activé' : 'Désactivé'],
         ['Internationalisation' => $additionalOptions['i18n'] ? 'Activée' : 'Désactivée'],
         ['Journalisation avancée' => $additionalOptions['logger'] ? 'Activée' : 'Désactivée'],
         ['Service de mail' => $additionalOptions['mail'] ? 'Activé' : 'Désactivé'],
         ['File d\'attente' => $additionalOptions['queue'] ? 'Activée' : 'Désactivée'],
         ['Paiement' => $additionalOptions['payment'] ? implode(', ', $additionalOptions['payment_providers'] ?? []) : 'Désactivé'],
         ['IA' => $additionalOptions['ai'] ? implode(', ', $additionalOptions['ai_providers'] ?? []) : 'Désactivée']
      );
   }

   private function applyConfiguration(
      SymfonyStyle $io,
      string $appName,
      string $appType,
      array $dbConfig,
      array $authSystem,
      string $cacheSystem,
      bool $useCraftPanel,
      array $additionalOptions,
      OutputInterface $output = null
   ): void {
      $io->section('Application de la configuration...');

      // Mise à jour du fichier .env
      $this->updateEnvFile($io, $appName, $appType, $dbConfig, $authSystem, $cacheSystem, $additionalOptions);

      // Mise à jour des configurations
      $this->updateAppConfig($io, $appName, $appType, $additionalOptions);
      $this->updateDatabaseConfig($io, $dbConfig);

      if ($additionalOptions['mail']) {
         $this->updateMailConfig($io);
      }

      // Installation des dépendances nécessaires
      $this->installDependencies($io, $authSystem, $cacheSystem, $useCraftPanel, $additionalOptions);

      // Publication des assets et configurations
      if ($useCraftPanel && $output !== null) {
         $io->text('Configuration du CraftPanel...');
         // Exécuter la commande d'installation du CraftPanel
         $craftPanelCommand = $this->getApplication()->find('craftpanel:install');
         $craftPanelCommand->run(new \Symfony\Component\Console\Input\ArrayInput([]), $output);
      }

      // Génération des fichiers de base
      $this->generateBaseFiles($io, $appType, $authSystem, $useCraftPanel);
   }

   private function updateEnvFile(
      SymfonyStyle $io,
      string $appName,
      string $appType,
      array $dbConfig,
      array $authSystem,
      string $cacheSystem,
      array $additionalOptions
   ): void {
      $io->text('Mise à jour du fichier .env...');

      $envPath = base_path('.env');
      if (!Filesystem::exists($envPath)) {
         Filesystem::copy(base_path('.env.example'), $envPath);
      }

      $env = Filesystem::get($envPath);

      // Mise à jour des variables d'environnement
      $replacements = [
         '/^APP_NAME=.*$/m' => 'APP_NAME="' . $appName . '"',
         '/^APP_ENV=.*$/m' => 'APP_ENV=local',
         '/^APP_DEBUG=.*$/m' => 'APP_DEBUG=true',
         '/^CACHE_DRIVER=.*$/m' => 'CACHE_DRIVER=' . $cacheSystem,
      ];

      // Configuration de la base de données
      if ($dbConfig['driver'] !== 'none') {
         $replacements['/^DB_CONNECTION=.*$/m'] = 'DB_CONNECTION=' . $dbConfig['driver'];

         if ($dbConfig['driver'] !== 'sqlite') {
            $replacements['/^DB_HOST=.*$/m'] = 'DB_HOST=' . $dbConfig['host'];
            $replacements['/^DB_PORT=.*$/m'] = 'DB_PORT=' . $dbConfig['port'];
            $replacements['/^DB_DATABASE=.*$/m'] = 'DB_DATABASE=' . $dbConfig['database'];
            $replacements['/^DB_USERNAME=.*$/m'] = 'DB_USERNAME=' . $dbConfig['username'];
            $replacements['/^DB_PASSWORD=.*$/m'] = 'DB_PASSWORD=' . $dbConfig['password'];
         } else {
            $replacements['/^DB_DATABASE=.*$/m'] = 'DB_DATABASE=' . $dbConfig['database'];
         }
      }

      // Configuration de l'authentification
      if ($authSystem['enabled']) {
         $replacements['/^AUTH_DRIVER=.*$/m'] = 'AUTH_DRIVER=' . $authSystem['driver'];
      }

      // Application des remplacements
      foreach ($replacements as $pattern => $replacement) {
         $env = preg_replace($pattern, $replacement, $env);
      }

      Filesystem::put($envPath, $env);
   }

   private function updateAppConfig(SymfonyStyle $io, string $appName, string $appType, array $additionalOptions): void
   {
      $io->text('Mise à jour de la configuration de l\'application...');

      $configPath = config_path('app.php');
      $config = include $configPath;

      // Mise à jour de la configuration
      $config['name'] = $appName;
      $config['locale'] = $additionalOptions['i18n'] ? ($additionalOptions['default_locale'] ?? 'fr') : 'fr';

      Filesystem::put($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");
   }

   private function updateDatabaseConfig(SymfonyStyle $io, array $dbConfig): void
   {
      if ($dbConfig['driver'] === 'none') {
         return;
      }

      $io->text('Mise à jour de la configuration de la base de données...');

      $configPath = config_path('database.php');
      $config = include $configPath;

      // Mise à jour de la configuration
      $config['default'] = $dbConfig['driver'];

      if (isset($dbConfig['host'])) {
         $config['connections'][$dbConfig['driver']]['host'] = $dbConfig['host'];
         $config['connections'][$dbConfig['driver']]['port'] = $dbConfig['port'];
         $config['connections'][$dbConfig['driver']]['database'] = $dbConfig['database'];
         $config['connections'][$dbConfig['driver']]['username'] = $dbConfig['username'];
         $config['connections'][$dbConfig['driver']]['password'] = $dbConfig['password'];
      } elseif ($dbConfig['driver'] === 'sqlite') {
         $config['connections']['sqlite']['database'] = $dbConfig['database'];
      }

      Filesystem::put($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");
   }

   private function updateMailConfig(SymfonyStyle $io): void
   {
      $io->text('Mise à jour de la configuration du service de mail...');

      // Configuration de base du service de mail
      $configPath = config_path('mail.php');
      $config = include $configPath;

      Filesystem::put($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");
   }

   private function installDependencies(
      SymfonyStyle $io,
      array $authSystem,
      string $cacheSystem,
      bool $useCraftPanel,
      array $additionalOptions
   ): void {
      $io->text('Installation des dépendances nécessaires...');

      $dependencies = [];

      // Dépendances pour l'authentification
      if ($authSystem['enabled']) {
         if ($authSystem['driver'] === 'token') {
            $dependencies[] = 'firebase/php-jwt';
         } elseif ($authSystem['driver'] === 'oauth') {
            $dependencies[] = 'league/oauth2-client';

            foreach ($authSystem['providers'] ?? [] as $provider) {
               $dependencies[] = "league/oauth2-{$provider}";
            }
         }
      }

      // Dépendances pour le cache
      if ($cacheSystem === 'redis') {
         $dependencies[] = 'predis/predis';
      } elseif ($cacheSystem === 'memcached') {
         // Memcached nécessite l'extension PHP
         $io->note('L\'utilisation de Memcached nécessite l\'extension PHP memcached.');
      }

      // Dépendances pour l'internationalisation
      if ($additionalOptions['i18n']) {
         $dependencies[] = 'symfony/translation';
      }

      // Dépendances pour la file d'attente
      if ($additionalOptions['queue']) {
         $dependencies[] = 'ironflow/queue';
      }

      // Dépendances pour le paiement
      if ($additionalOptions['payment']) {
         if (in_array('stripe', $additionalOptions['payment_providers'] ?? [])) {
            $dependencies[] = 'stripe/stripe-php';
         }
         if (in_array('paypal', $additionalOptions['payment_providers'] ?? [])) {
            $dependencies[] = 'paypal/rest-api-sdk-php';
         }
      }

      // Dépendances pour l'IA
      if ($additionalOptions['ai']) {
         if (in_array('chatgpt', $additionalOptions['ai_providers'] ?? [])) {
            $dependencies[] = 'openai-php/client';
         }
         if (in_array('claude', $additionalOptions['ai_providers'] ?? [])) {
            $dependencies[] = 'anthropic/anthropic';
         }
      }

      // Installation des dépendances si nécessaire
      if (!empty($dependencies)) {
         $io->text('Les dépendances suivantes seront installées: ' . implode(', ', $dependencies));
         $io->note('Pour installer les dépendances, exécutez: composer require ' . implode(' ', $dependencies));
      }
   }

   private function generateBaseFiles(SymfonyStyle $io, string $appType, array $authSystem, bool $useCraftPanel): void
   {
      $io->text('Génération des fichiers de base...');

      // Création de la structure de base selon le type d'application
      if ($appType === 'web' || $appType === 'full') {
         $this->generateWebBase($io);
      }

      if ($appType === 'api' || $appType === 'full') {
         $this->generateApiBase($io);
      }

      // Génération des fichiers d'authentification si activée
      if ($authSystem['enabled']) {
         $this->generateAuthFiles($io, $authSystem['driver']);
      }

      // Génération des fichiers de base pour le CraftPanel si activé
      if ($useCraftPanel) {
         $this->generateCraftPanelFiles($io);
      }
   }

   private function generateWebBase(SymfonyStyle $io): void
   {
      $io->text('Génération de la structure de base pour l\'application web...');

      // Création des fichiers de base (contrôleurs, vues, routes)
      // Code pour générer les fichiers web de base
   }

   private function generateApiBase(SymfonyStyle $io): void
   {
      $io->text('Génération de la structure de base pour l\'API...');

      // Création des fichiers de base pour l'API (contrôleurs, routes, etc.)
      // Code pour générer les fichiers API de base
   }

   private function generateAuthFiles(SymfonyStyle $io, string $driver): void
   {
      $io->text("Génération des fichiers d'authentification ({$driver})...");

      // Génération des fichiers d'authentification selon le driver
      // Code pour générer les fichiers d'authentification
   }

   private function generateCraftPanelFiles(SymfonyStyle $io): void
   {
      $io->text('Génération des fichiers pour le CraftPanel...');

      // Génération des fichiers pour le CraftPanel
      // Code pour générer les fichiers CraftPanel
   }
}
