<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Auth;

use IronFlow\Database\Migrations\Migration;
use IronFlow\Support\Facades\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuthInstallCommand extends Command
{
    protected static $defaultName = 'auth:setup';
    protected static $defaultDescription = 'Install authentication system';


    public function configure(): void
    {
        $this->addOption('user-model', 'um', InputOption::VALUE_NONE, 'Modèle à utiliser pour l\'authentification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Configuration du système d\'authentification de IronFlow');

        $model = $io->ask('Quel modèle souhaitez-vous utiliser pour l\'authentification ?', 'User');
        if ($input->getOption('user-model')) {
            $model = $input->getOption('user-model');
        }
        if (!class_exists($model)) {
            $io->error("Le modèle {$model} n'existe pas. Veuillez le créer ou choisir un autre modèle.");
            return Command::FAILURE;
        }

        $authSystem = $this->askAuthSystem($io, $model);

        $this->recap($io, $authSystem);

        // Create migrations
        $this->createMigrations($io);

        // Copy views
        $this->copyViews();

        // Copy controllers
        $this->copyControllers();

        // Add routes
        $this->addRoutes();

        $io->success('Système d\'authentification installé avec succès.');
        $io->note('Veuillez taper la commande suivante pour executer les migrations :');
        $io->writeln('php forge migrate');

        return Command::SUCCESS;
    }

    private function recap(SymfonyStyle $io, array $authSystem)
    {
        $io->section('Récapitulatif');
        $io->table(['Option', 'Valeur'], $authSystem);

        if ($io->confirm('Voulez-vous continuer ?')) {
            return;
        }

        return Command::FAILURE;
    }

    private function askAuthSystem(SymfonyStyle $io, string $model): array
    {
        $driver = $io->choice('Quel type d\'authentification souhaitez-vous utiliser ?', [
            'guard' => 'Guard (système avancé)',
            'oauth' => 'OAuth (authentification sociale)'
        ], 'session');

        if ($driver === 'guard') {
            $guard = $io->choice('Quel type de guard souhaitez-vous utiliser ?', [
                'session' => 'Session (pour applications web)',
                'jwt' => 'Token JWT (pour API)',
            ], 'session');
        } else {
            $guard = null;
        }

        if ($driver === 'oauth') {
            $io->note('Pour utiliser OAuth, vous devez configurer les clés API pour chaque fournisseur dans le fichier .env.');
        }

        $config = [
            'enabled' => true,
            'driver' => $driver,
            'guard' => $guard,
            'model' => $model,
            'providers' => [
                'users' => [
                    'driver' => 'database',
                    'model' => $model,
                ], 
            ],
            'passwords' => [
                'users' => [
                    'provider' => 'users',
                    'table' => 'password_resets',
                    'expire' => 60,
                ],
            ],
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

    protected function createMigrations(SymfonyStyle $io): void
    {
        // Create users table
        $migration = <<<PHP
         <?php

         use IronFlow\Database\Migrations\Migration;
         use IronFlow\Database\Schema\Schema;
         use IronFlow\Database\Schema\Anvil;

         return new class extends Migration {
            Schema::createTable('users', function (Anvil \$table) {
                \$table->id();
                \$table->string('name');
                \$table->string('email')->unique();
                \$table->string('password');
                \$table->string('remember_token')->nullable();
                \$table->timestamps();
            });

            Schema::createTable('password_resets', function (Anvil \$table) {
                \$table->string('email')->index();
                \$table->string('token');
                \$table->timestamp('created_at')->nullable();
            });

        };
PHP;

        $file = Filesystem::put(base_path('database/migrations/' . now()->format('Y_m_d_His') . '_create_users_table.php'), $migration);

        $io->success('Migrations créées avec succès.');

    }

    protected function copyViews(): void
    {
        $views = [
            'auth/login.php',
            'auth/register.php',
            'auth/forgot-password.php',
            'auth/reset-password.php',
            'auth/verify-email.php',
        ];

        foreach ($views as $view) {
            $this->copyStub(
                "stubs/auth/views/{$view}",
                "resources/views/{$view}"
            );
        }
    }

    protected function copyControllers(): void
    {
        $controllers = [
            'Auth/LoginController.php',
            'Auth/RegisterController.php',
            'Auth/ForgotPasswordController.php',
            'Auth/ResetPasswordController.php',
            'Auth/VerificationController.php',
        ];

        foreach ($controllers as $controller) {
            $this->copyStub(
                "stubs/auth/controllers/{$controller}",
                "app/Controllers/{$controller}"
            );
        }
    }

    protected function addRoutes(): void
    {
        $routes = Filesystem::get(base_path('routes/web.php'));
        $authRoutes = Filesystem::get(__DIR__ . '/stubs/auth/routes.stub');

        Filesystem::put(
            base_path('routes/web.php'),
            $routes . "\n" . $authRoutes
        );
    }

    protected function copyStub(string $stub, string $target): void
    {
        if (Filesystem::exists(base_path($target))) {
            Filesystem::makeDirectory(dirname($target), 0755, true);
        }

        Filesystem::copy(
            __DIR__ . '/' . $stub,
            base_path($target)
        );
    }
}
