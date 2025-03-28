<?php

namespace IronFlow\Console\Commands\CraftPanel;

use IronFlow\Console\Commands\Command;
use IronFlow\Support\Filesystem;
use IronFlow\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected $signature = 'craftpanel:install {--force : Force l\'installation même si le CraftPanel existe déjà}';

    protected $description = 'Installer et configurer le CraftPanel';

    public function handle(): int
    {
        $this->info('Installation du CraftPanel...');

        // Vérifier si le CraftPanel est déjà installé
        if (!$this->option('force') && $this->isInstalled()) {
            $this->error('Le CraftPanel est déjà installé. Utilisez --force pour réinstaller.');
            return 1;
        }

        // Créer les dossiers nécessaires
        $this->createDirectories();

        // Générer les fichiers de configuration
        $this->generateConfig();

        // Publier les assets
        $this->publishAssets();

        // Créer les routes
        $this->createRoutes();

        // Créer les vues
        $this->createViews();

        // Créer les composants
        $this->createComponents();

        $this->info('Installation terminée avec succès !');
        return 0;
    }

    private function isInstalled(): bool
    {
        return file_exists(base_path('config/craftpanel.php'));
    }

    private function createDirectories(): void
    {
        $directories = [
            'config',
            'public/css',
            'public/js',
            'resources/views/craftpanel',
            'resources/views/craftpanel/layouts',
            'resources/views/craftpanel/components',
        ];

        foreach ($directories as $directory) {
            Filesystem::ensureDirectoryExists(base_path($directory));
        }
    }

    private function generateConfig(): void
    {
        $config = [
            'title' => env('APP_NAME', 'CraftPanel'),
            'prefix' => 'craftpanel',
            'middleware' => ['web', 'auth'],
        ];

        Filesystem::put(base_path('config/craftpanel.php'), var_export($config, true));
    }

    private function publishAssets(): void
    {
        // Copier les assets par défaut
        Filesystem::copyDirectory(
            __DIR__ . '/../../Resources/assets',
            public_path('craftpanel')
        );
    }

    private function createRoutes(): void
    {
        $routes = <<<'ROUTES'
<?php

use IronFlow\Http\Routing\Router;
use IronFlow\CraftPanel\Controllers\CraftPanelController;

Router::prefix('craftpanel')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Router::get('/', [CraftPanelController::class, 'dashboard'])
            ->name('craftpanel.dashboard');

        Router::get('/{model}', [CraftPanelController::class, 'index'])
            ->name('craftpanel.index');

        Router::get('/{model}/create', [CraftPanelController::class, 'create'])
            ->name('craftpanel.create');

        Router::post('/{model}', [CraftPanelController::class, 'store'])
            ->name('craftpanel.store');

        Router::get('/{model}/{id}/edit', [CraftPanelController::class, 'edit'])
            ->name('craftpanel.edit');

        Router::put('/{model}/{id}', [CraftPanelController::class, 'update'])
            ->name('craftpanel.update');

        Router::delete('/{model}/{id}', [CraftPanelController::class, 'destroy'])
            ->name('craftpanel.destroy');

        Router::get('/settings', [CraftPanelController::class, 'settings'])
            ->name('craftpanel.settings');

        Router::post('/settings', [CraftPanelController::class, 'updateSettings'])
            ->name('craftpanel.updateSettings');
    });
ROUTES;

        Filesystem::put(base_path('routes/craftpanel.php'), $routes);
    }

    private function createViews(): void
    {
        // Copier les vues par défaut
        Filesystem::copyDirectory(
            __DIR__ . '/../../Resources/views',
            resource_path('views/craftpanel')
        );
    }

    private function createComponents(): void
    {
        // Copier les composants par défaut
        Filesystem::copyDirectory(
            __DIR__ . '/../../Resources/views/components',
            resource_path('views/craftpanel/components')
        );
    }
}
