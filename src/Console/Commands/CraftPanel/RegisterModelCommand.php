<?php

namespace IronFlow\Console\Commands\CraftPanel;

use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegisterModelCommand extends Command
{
    protected static $defaultName = 'craft:register';
    protected static $defaultDescription = 'Enregistre un modèle dans le CraftPanel';

    protected function configure(): void
    {
        $this
            ->addArgument('model', InputArgument::REQUIRED, 'Nom du modèle à enregistrer')
            ->addOption('icon', null, InputOption::VALUE_OPTIONAL, 'Icône pour le modèle', 'database')
            ->addOption('display-name', null, InputOption::VALUE_OPTIONAL, 'Nom d\'affichage du modèle')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer la réinscription du modèle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupération des arguments et options
        $model = $input->getArgument('model');
        $icon = $input->getOption('icon');
        $displayName = $input->getOption('display-name') ?? str_replace('\\', ' ', $model);
        $force = $input->getOption('force');

        // Vérification de l'existence du modèle
        if (!class_exists($model)) {
            $io->error("Le modèle {$model} n'existe pas.");
            return Command::FAILURE;
        }

        // Vérification si le modèle est déjà enregistré
        $configPath = config_path('craft.php');
        if (!$force && $this->isModelRegistered($model, $configPath)) {
            $io->error("Le modèle {$model} est déjà enregistré. Utilisez --force pour réenregistrer.");
            return Command::FAILURE;
        }

        // Enregistrement du modèle
        $this->registerModel($model, $icon, $displayName, $configPath);
        
        $io->success("Le modèle {$model} a été enregistré avec succès dans le CraftPanel !");
        return Command::SUCCESS;
    }

    protected function isModelRegistered(string $model, string $configPath): bool
    {
        if (!Filesystem::exists($configPath)) {
            return false;
        }

        $config = require $configPath;
        return isset($config['models'][$model]);
    }

    protected function registerModel(string $model, string $icon, string $displayName, string $configPath): void
    {
        $config = Filesystem::exists($configPath) ? require $configPath : ['models' => []];
        
        $config['models'][$model] = [
            'icon' => $icon,
            'display_name' => $displayName,
            'permissions' => [
                'view' => true,
                'create' => true,
                'edit' => true,
                'delete' => true,
            ]
        ];

        if (!Filesystem::exists(dirname($configPath))) {
            Filesystem::makeDirectory(dirname($configPath), 0755, true);
        }

        Filesystem::put($configPath, '<?php return ' . var_export($config, true) . ';');
    }
}
