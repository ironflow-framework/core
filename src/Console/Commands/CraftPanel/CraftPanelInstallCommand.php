<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Filesystem;

class CraftPanelInstallCommand extends Command
{
    protected static $defaultName = 'craftpanel:install';
    protected static $defaultDescription = 'Installer et configurer le CraftPanel';

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer l\'installation même si le CraftPanel existe déjà')
            ->addOption('config', 'c', InputOption::VALUE_NONE, 'Générer uniquement la configuration')
            ->addOption('routes', 'r', InputOption::VALUE_NONE, 'Générer uniquement les routes')
            ->addOption('views', 'v', InputOption::VALUE_NONE, 'Générer uniquement les vues')
            ->addOption('components', 'cp', InputOption::VALUE_NONE, 'Générer uniquement les composants');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $configOnly = $input->getOption('config');
        $routesOnly = $input->getOption('routes');
        $viewsOnly = $input->getOption('views');
        $componentsOnly = $input->getOption('components');

        // Vérifier si le CraftPanel est déjà installé
        if (!$force && $this->isInstalled()) {
            $io->error('Le CraftPanel est déjà installé. Utilisez --force pour réinstaller.');
            return Command::FAILURE;
        }

        // Déterminer les étapes à réaliser
        $completeInstall = !($configOnly || $routesOnly || $viewsOnly || $componentsOnly);

        try {
            if ($completeInstall || $configOnly) {
                $this->generateConfig($io);
            }

            if ($completeInstall || $routesOnly) {
                $this->createRoutes($io);
            }

            if ($completeInstall || $viewsOnly) {
                $this->createViews($io);
            }

            if ($completeInstall || $componentsOnly) {
                $this->createComponents($io);
            }

            if ($completeInstall) {
                $this->createDirectories($io);
                $this->publishAssets($io);
            }

            $io->success('Installation du CraftPanel terminée avec succès !');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'installation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function isInstalled(): bool
    {
        return file_exists(base_path('config/craftpanel.php'));
    }

    protected function createDirectories(SymfonyStyle $io): void
    {
        $directories = [
            'config',
            'resources/css/craftpanel',
            'resources/js/craftpanel',
            'resources/views/craftpanel/layouts',
            'resources/views/craftpanel/components',
        ];

        foreach ($directories as $directory) {
            Filesystem::ensureDirectoryExists(base_path($directory));
        }

        $io->note('Répertoires du CraftPanel créés.');
    }

    protected function generateConfig(SymfonyStyle $io): void
    {
        $config = $this->generateConfigContent();

        $configPath = base_path('config/craftpanel.php');
        Filesystem::put($configPath, $config);

        $io->success("Fichier de configuration CraftPanel généré à {$configPath}");
    }

    protected function generateConfigContent(): string
    {
        return <<<PHP
<?php

return [
    'title' => env('APP_NAME', 'CraftPanel'),
    'prefix' => 'craftpanel',
    'middleware' => ['web', 'auth'],
    // Configuration personnalisable
];
PHP;
    }

    protected function publishAssets(SymfonyStyle $io): void
    {
        Filesystem::copy(
            __DIR__ . '/../../Resources/assets',
            public_path('craftpanel')
        );

        $io->note('Assets du CraftPanel publiés.');
    }

    protected function createRoutes(SymfonyStyle $io): void
    {
        $routesContent = $this->generateRoutesContent();
        $routesPath = base_path('routes/craftpanel.php');

        Filesystem::put($routesPath, $routesContent);

        $io->success("Routes du CraftPanel générées à {$routesPath}");
    }

    protected function generateRoutesContent(): string
    {
        return <<<'ROUTES'
<?php

use IronFlow\Http\Routing\Router;
use IronFlow\CraftPanel\Controllers\CraftPanelController;

Router::prefix('craftpanel')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Router::get('/', [CraftPanelController::class, 'dashboard'])
            ->name('craftpanel.dashboard');

        Router::resourceRoutes('models', CraftPanelController::class);
    });
ROUTES;
    }

    protected function createViews(SymfonyStyle $io): void
    {
        Filesystem::copy(
            __DIR__ . '/../../Resources/views',
            resource_path('views/craftpanel')
        );

        $io->note('Vues du CraftPanel créées.');
    }

    protected function createComponents(SymfonyStyle $io): void
    {
        Filesystem::copy(
            __DIR__ . '/../../Resources/views/components',
            resource_path('views/craftpanel/components')
        );

        $io->note('Composants du CraftPanel créés.');
    }
}
