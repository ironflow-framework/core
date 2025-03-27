<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands;

use IronFlow\CraftPanel\CraftPanelServiceProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class CraftPanelInstallCommand extends Command
{
    protected string $signature = 'craft:panel:install';
    protected string $description = 'Install and configure the CraftPanel administration interface';

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the installation');
    }

    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installation de CraftPanel');

        // Créer les dossiers nécessaires
        $this->createDirectories($io);
        $io->success('Dossiers créés avec succès!');

        // Publier les assets
        $this->publishAssets($io);
        $io->success('Assets publiés avec succès!');

        // Publier la configuration
        $this->publishConfig($io);

        // Créer les migrations
        $this->createMigrations($io);
        $io->success('Migrations créées avec succès!');

        $io->success('CraftPanel a été installé avec succès!');
        $io->writeln('Exécutez les migrations avec: php forge migrate');
        
        return Command::SUCCESS;
    }

    protected function createDirectories(SymfonyStyle $io): void
    {
        $directories = [
            'app/CraftPanel',
            'app/CraftPanel/Resources',
            'app/CraftPanel/Resources/views',
            'app/CraftPanel/Resources/js',
            'app/CraftPanel/Resources/css',
            'app/CraftPanel/Controllers',
            'app/CraftPanel/Models',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
                $io->success("Created directory: {$directory}");
            }
        }
    }

    protected function publishAssets(SymfonyStyle $io): void
    {
        // Copier les assets (JS, CSS, images)
        $io->success('Publishing assets...');
        // TODO: Implémenter la copie des assets
    }

    protected function publishConfig(SymfonyStyle $io): void
    {
        $configPath = 'config/craftpanel.php';
        if (!file_exists($configPath)) {
            $content = <<<PHP
<?php

return [
    'route_prefix' => 'admin',
    'middleware' => ['web', 'auth', 'admin'],
    'guard' => 'web',
    'title' => 'CraftPanel',
    'models_path' => 'app/Models',
    'layout' => 'craftpanel::layouts.app',
    'menu' => [
        'dashboard' => [
            'icon' => 'dashboard',
            'route' => 'craftpanel.dashboard',
        ],
    ],
    'auth' => [
        'controller' => \IronFlow\CraftPanel\Http\Controllers\Auth\LoginController::class,
        'guard' => 'admin',
        'passwords' => 'admins',
    ],
];
PHP;
            file_put_contents($configPath, $content);
            $io->success("Fichier de configuration créé: {$configPath}");
        }
    }

    protected function createMigrations(SymfonyStyle $io): void
    {
        $timestamp = date('Y_m_d_His');
        $migration = <<<PHP
<?php

use IronFlow\Database\Iron\Migration;
use IronFlow\Database\Iron\Schema\Schema;
use IronFlow\Database\Iron\Schema\Anvil;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Anvil \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->string('password');
            \$table->rememberToken();
            \$table->timestamps();
        });

        Schema::create('admin_roles', function (Anvil \$table) {
            \$table->id();
            \$table->string('name')->unique();
            \$table->string('description')->nullable();
            \$table->timestamps();
        });

        Schema::create('admin_permissions', function (Schema \$table) {
            \$table->id();
            \$table->string('name')->unique();
            \$table->string('description')->nullable();
            \$table->timestamps();
        });

        Schema::create('admin_role_permissions', function (Schema \$table) {
            \$table->foreignId('role_id')->constrained('admin_roles')->onDelete('cascade');
            \$table->foreignId('permission_id')->constrained('admin_permissions')->onDelete('cascade');
            \$table->primary(['role_id', 'permission_id']);
        });

        Schema::create('admin_user_roles', function (Schema \$table) {
            \$table->foreignId('user_id')->constrained('admin_users')->onDelete('cascade');
            \$table->foreignId('role_id')->constrained('admin_roles')->onDelete('cascade');
            \$table->primary(['user_id', 'role_id']);
        });

        Schema::create('admin_activity_log', function (Schema \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained('admin_users')->onDelete('cascade');
            \$table->string('action');
            \$table->string('model_type');
            \$table->unsignedBigInteger('model_id');
            \$table->json('changes')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_log');
        Schema::dropIfExists('admin_user_roles');
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('admin_users');
    }
};
PHP;

        $migrationPath = "database/migrations/{$timestamp}_create_craftpanel_tables.php";
        file_put_contents($migrationPath, $migration);
        $io->success("Created migration: {$migrationPath}");
    }
}
