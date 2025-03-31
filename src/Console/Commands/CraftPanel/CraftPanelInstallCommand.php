<?php

namespace IronFlow\Console\Commands\CraftPanel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Facades\Filesystem;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\DB;

class CraftPanelInstallCommand extends Command
{
    protected static $defaultName = 'craftpanel:install';
    protected static $defaultDescription = 'Installer et configurer le CraftPanel';

    protected function configure(): void
    {
        $this
            ->addOption('db-name', null, InputOption::VALUE_OPTIONAL, 'Nom de la base de données', 'craftpanel')
            ->addOption('db-user', null, InputOption::VALUE_OPTIONAL, 'Utilisateur de la base de données', 'root')
            ->addOption('db-password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe de la base de données', '')
            ->addOption('theme', null, InputOption::VALUE_OPTIONAL, 'Thème à utiliser (light/dark)', 'light')
            ->addOption('security-level', null, InputOption::VALUE_OPTIONAL, 'Niveau de sécurité (low/medium/high)', 'medium')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'installation même si le CraftPanel existe déjà');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installation du CraftPanel');

        // Vérification des prérequis
        if (!$this->checkPrerequisites($io)) {
            return Command::FAILURE;
        }

        // Configuration de la base de données
        if (!$this->setupDatabase($io, $input)) {
            return Command::FAILURE;
        }

        // Installation des fichiers
        if (!$this->installFiles($io)) {
            return Command::FAILURE;
        }

        // Configuration de l'authentification
        if (!$this->setupAuthentication($io)) {
            return Command::FAILURE;
        }

        // Installation des assets
        if (!$this->installAssets($io, $input)) {
            return Command::FAILURE;
        }

        $io->success('Installation du CraftPanel terminée avec succès !');
        return Command::SUCCESS;
    }

    protected function checkPrerequisites(SymfonyStyle $io): bool
    {
        $io->section('Vérification des prérequis');

        // Vérifier PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            $io->error('PHP 8.2 ou supérieur est requis.');
            return false;
        }

        // Vérifier les extensions PHP nécessaires
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $io->error("L'extension PHP {$extension} est requise.");
                return false;
            }
        }

        $io->success('✓ Prérequis vérifiés avec succès.');
        return true;
    }

    protected function setupDatabase(SymfonyStyle $io, InputInterface $input): bool
    {
        $io->section('Configuration de la base de données');

        $dbName = $input->getOption('db-name') ?? env('DB_DATABASE', 'ironflow');

        try {
            // Création de la base de données si elle n'existe pas
            DB::getInstance()->query("CREATE DATABASE IF NOT EXISTS {$dbName}");
            DB::getInstance()->query("USE {$dbName}");

            // Création des tables nécessaires
            $this->createTables();

            $io->success('✓ Base de données configurée avec succès.');
            return true;
        } catch (\Exception $e) {
            $io->error("Erreur lors de la configuration de la base de données : " . $e->getMessage());
            return false;
        }
    }

    protected function createTables(): void
    {
        // Création des tables pour le CraftPanel
        $query = "
            CREATE TABLE IF NOT EXISTS craftpanel_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                key_name VARCHAR(255) NOT NULL,
                value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        $stmt = DB::getInstance()->getConnection()->prepare($query);
        $stmt->execute();
    }

    protected function installFiles(SymfonyStyle $io): bool
    {
        $io->section('Installation des fichiers');

        $authConfigFile = config_path('auth.php');
        if (file_exists($authConfigFile)) {
            $authConfig = require $authConfigFile;
            $authConfig['providers']['roles']['driver'] = 'database';
            $authConfig['providers']['roles']['model'] = App\Models\Role::class;
            $authConfig['providers']['permissions']['driver'] = 'database';
            $authConfig['providers']['permissions']['model'] = App\Models\Permission::class;
            file_put_contents($authConfigFile, '<?php return ' . var_export($authConfig, true) . ';');
        } else {
            $io->error("Erreur lors de la mise à jour du fichier {$authConfigFile}.");
            
        }

        $files = [
            'config/craftpanel.php' => base_path('config/craftpanel.php'),
            'resources/views/craftpanel' => resource_path('views/craftpanel'),
            'routes/craftpanel.php' => base_path('routes/craftpanel.php'),
        ];

        foreach ($files as $source => $destination) {
            try {
                Filesystem::copyDirectory($source, $destination);
                $io->text("✓ Fichier {$destination} installé.");
            } catch (\Exception $e) {
                $io->error("Erreur lors de l'installation du fichier {$destination} : " . $e->getMessage());
                return false;
            }
        }

        $io->success('✓ Fichiers installés avec succès.');

        return true;
    }

    protected function setupAuthentication(SymfonyStyle $io): bool
    {
        $io->section('Configuration de l\'authentification');

        try {
            // Intégration avec le système d'authentification existant
            $authConfig = [
                'guard' => 'craftpanel',
                'provider' => 'users',
                'middleware' => ['web', 'auth'],
            ];

            Config::set('auth.guards.craftpanel', $authConfig);

            $io->success('✓ Authentification configurée avec succès.');
            return true;
        } catch (\Exception $e) {
            $io->error("Erreur lors de la configuration de l'authentification : " . $e->getMessage());
            return false;
        }
    }

    protected function installAssets(SymfonyStyle $io, InputInterface $input): bool
    {
        $io->section('Installation des assets');

        try {
            $theme = $input->getOption('theme');
            $securityLevel = $input->getOption('security-level');

            // Copie des assets selon le thème choisi
            Filesystem::copyDirectory(
                __DIR__ . '/../../../resources/assets/craftpanel/themes/' . $theme,
                public_path('assets/craftpanel')
            );

            // Configuration de la sécurité
            $this->configureSecurity($securityLevel);

            $io->success('✓ Assets installés avec succès.');
            return true;
        } catch (\Exception $e) {
            $io->error("Erreur lors de l'installation des assets : " . $e->getMessage());
            return false;
        }
    }

    protected function configureSecurity(string $level): void
    {
        $securityConfig = [
            'low' => [
                'session_lifetime' => 120,
                'password_min_length' => 8,
                'require_2fa' => false,
            ],
            'medium' => [
                'session_lifetime' => 60,
                'password_min_length' => 12,
                'require_2fa' => true,
            ],
            'high' => [
                'session_lifetime' => 30,
                'password_min_length' => 16,
                'require_2fa' => true,
                'require_strong_password' => true,
            ],
        ];

        Config::set('craftpanel.security', $securityConfig[$level]);
    }
}
