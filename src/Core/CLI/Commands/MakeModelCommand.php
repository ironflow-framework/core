<?php 

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: make:model
 */
class MakeModelCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:model')
             ->setDescription('Create a new model')
             ->addArgument('name', InputArgument::REQUIRED, 'Model name')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module name')
             ->addOption('migration', null, InputOption::VALUE_NONE, 'Also create migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = $input->getOption('module');
        $withMigration = $input->getOption('migration');

        if ($module) {
            $namespace = "App\\Modules\\{$module}\\Models";
            $path = "modules/{$module}/Models/{$name}.php";
        } else {
            $namespace = "App\\Models";
            $path = "app/Models/{$name}.php";
        }

        $tableName = strtolower($name) . 's';

        $content = $this->generateFromTemplate('Model', [
            'CLASS_NAME' => $name,
            'NAMESPACE' => $namespace,
            'TABLE_NAME' => $tableName
        ]);

        $this->writeFile($path, $content, $output);

        if ($withMigration) {
            $migrationName = "create_{$tableName}_table";
            $this->createMigration($migrationName, $module, $output);
        }

        $output->writeln("<comment>Model {$name} created successfully!</comment>");
        return Command::SUCCESS;
    }

    private function createMigration(string $name, ?string $module, OutputInterface $output): void
    {
        $timestamp = date('Y_m_d_His');
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        
        if ($module) {
            $namespace = "App\\Modules\\{$module}\\Database\\Migrations";
            $path = "modules/{$module}/database/migrations/{$timestamp}_{$name}.php";
        } else {
            $namespace = "App\\Database\\Migrations";
            $path = "database/migrations/{$timestamp}_{$name}.php";
        }

        $content = $this->generateFromTemplate('Migration', [
            'CLASS_NAME' => $className,
            'NAMESPACE' => $namespace,
            'TABLE_NAME' => str_replace(['create_', '_table'], '', $name)
        ]);

        $this->writeFile($path, $content, $output);
    }
}
