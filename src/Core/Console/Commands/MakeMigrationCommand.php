<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: make:migration
 */
class MakeMigrationCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:migration')
             ->setDescription('Create a new migration')
             ->addArgument('name', InputArgument::REQUIRED, 'Migration name')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module name');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = $input->getOption('module');
        
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
            'TABLE_NAME' => 'table_name' // À personnaliser selon le pattern du nom
        ]);

        $this->writeFile($path, $content);

        $output->writeln("<comment>Migration {$className} created successfully!</comment>");
        return Command::SUCCESS;
    }
}