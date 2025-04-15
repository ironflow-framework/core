<?php

namespace IronFlow\Console\Commands\CraftPanel;

use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends Command
{
    protected static $defaultName = 'craft:install';
    protected static $defaultDescription = 'Installe et initialise le CraftPanel';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installation du CraftPanel');

        try {
            // Copier les assets
            $this->copyAssets($io);

            // Publier la configuration
            $this->publishConfig($io);

            $io->success('CraftPanel a été installé avec succès !');
            $io->text('Vous pouvez maintenant créer un administrateur avec la commande : php forge craft:make-admin');
            $io->text('Accédez au panel à l\'URL : /craft');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'installation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function copyAssets(SymfonyStyle $io): void
    {
        $sourcePath = __DIR__ . '/../../../../resources/assets/craft';
        $targetPath = public_path('assets/craft');

        if (!Filesystem::exists($targetPath)) {
            Filesystem::makeDirectory($targetPath, 0755, true);
        }

        Filesystem::copyDirectory($sourcePath, $targetPath);
        $io->text('Assets copiés avec succès.');
    }

    protected function publishConfig(SymfonyStyle $io): void
    {
        $sourcePath = __DIR__ . '/../../../../config/craft.php';
        $targetPath = config_path('craft.php');

        if (!Filesystem::exists(dirname($targetPath))) {
            Filesystem::makeDirectory(dirname($targetPath), 0755, true);
        }

        if (!Filesystem::exists($sourcePath)) {
            $this->createDefaultConfig($sourcePath);
        }

        Filesystem::copy($sourcePath, $targetPath);
        $io->text('Configuration publiée avec succès.');
    }

    protected function createDefaultConfig(string $path): void
    {
        $config = [
            'title' => 'CraftPanel',
            'route_prefix' => 'craft',
            'middleware' => ['web', 'auth', 'admin'],
            'models' => [],
            'menu' => [
                'dashboard' => [
                    'icon' => 'dashboard',
                    'title' => 'Tableau de bord',
                    'route' => 'craft.dashboard'
                ]
            ]
        ];

        Filesystem::put($path, '<?php return ' . var_export($config, true) . ';');
    }
}
