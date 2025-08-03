<?php

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: make:module
 */
class MakeModuleCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:module')
             ->setDescription('Create a new module')
             ->addArgument('name', InputArgument::REQUIRED, 'Module name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $modulePath = "modules/{$name}";

        // Créer la structure du module
        $this->ensureDirectoryExists($modulePath);
        $this->ensureDirectoryExists("{$modulePath}/Controllers");
        $this->ensureDirectoryExists("{$modulePath}/Services");
        $this->ensureDirectoryExists("{$modulePath}/Models");
        $this->ensureDirectoryExists("{$modulePath}/database/migrations");
        $this->ensureDirectoryExists("{$modulePath}/database/seeders");
        $this->ensureDirectoryExists("{$modulePath}/database/factories");

        // Générer le ModuleProvider
        $providerContent = $this->generateFromTemplate('ModuleProvider', [
            'MODULE_NAME' => $name,
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/{$name}ModuleProvider.php", $providerContent, $output);

        // Générer le fichier de routes
        $routesContent = $this->generateFromTemplate('routes', [
            'MODULE_NAME' => $name
        ]);

        $this->writeFile("{$modulePath}/routes.php", $routesContent, $output);

        $output->writeln("<comment>Module {$name} created successfully!</comment>");
        $output->writeln("<info>Don't forget to register it in bootstrap/app.php</info>");

        return Command::SUCCESS;
    }
}
