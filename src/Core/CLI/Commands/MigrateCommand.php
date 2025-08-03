<?php

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Commande: migrate
 */
class MigrateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('migrate')
             ->setDescription('Run database migrations')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Run migrations for specific module only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = $input->getOption('module');
        
        $output->writeln('<info>Running migrations...</info>');

        // Créer la table des migrations si elle n'existe pas
        $this->createMigrationsTable($output);

        if ($module) {
            $this->runModuleMigrations($module, $output);
        } else {
            $this->runAllMigrations($output);
        }

        $output->writeln('<comment>Migrations completed!</comment>');
        return Command::SUCCESS;
    }

    private function createMigrationsTable(OutputInterface $output): void
    {
        // Implémentation de la création de la table migrations
        $output->writeln('<info>Ensuring migrations table exists...</info>');
        // TODO: Implémenter avec PDO
    }

    private function runAllMigrations(OutputInterface $output): void
    {
        // Migrations globales
        $this->runMigrationsInDirectory('database/migrations', $output);

        // Migrations des modules
        $modulesPath = 'modules';
        if (is_dir($modulesPath)) {
            $modules = scandir($modulesPath);
            foreach ($modules as $module) {
                if ($module !== '.' && $module !== '..' && is_dir("{$modulesPath}/{$module}")) {
                    $this->runMigrationsInDirectory("modules/{$module}/database/migrations", $output);
                }
            }
        }
    }

    private function runModuleMigrations(string $module, OutputInterface $output): void
    {
        $this->runMigrationsInDirectory("modules/{$module}/database/migrations", $output);
    }

    private function runMigrationsInDirectory(string $directory, OutputInterface $output): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob("{$directory}/*.php");
        sort($files);

        foreach ($files as $file) {
            $migration = basename($file, '.php');
            
            // Vérifier si la migration a déjà été exécutée
            if (!$this->migrationExists($migration)) {
                $this->runMigration($file, $migration, $output);
            }
        }
    }

    private function migrationExists(string $migration): bool
    {
        // TODO: Vérifier dans la table migrations
        return false;
    }

    private function runMigration(string $file, string $migration, OutputInterface $output): void
    {
        require_once $file;
        
        // Extraire le nom de classe du fichier
        $className = $this->getMigrationClassName($file);
        
        if (class_exists($className)) {
            $migrationInstance = new $className();
            $migrationInstance->up();
            
            // Enregistrer dans la table migrations
            $this->recordMigration($migration);
            
            $output->writeln("<info>Migrated: {$migration}</info>");
        }
    }

    private function getMigrationClassName(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class\s+(\w+)/', $content, $matches);
        return $matches[1] ?? '';
    }

    private function recordMigration(string $migration): void
    {
        // TODO: Insérer dans la table migrations
    }
}
