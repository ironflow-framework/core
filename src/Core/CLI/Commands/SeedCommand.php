<?php

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: db:seed
 */
class SeedCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('db:seed')
             ->setDescription('Run database seeders')
             ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Seeder class to run')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module to seed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getOption('class');
        $module = $input->getOption('module');

        $output->writeln('<info>Seeding database...</info>');

        if ($class) {
            $this->runSeeder($class, $output);
        } else {
            $this->runAllSeeders($module, $output);
        }

        $output->writeln('<comment>Database seeded!</comment>');
        return Command::SUCCESS;
    }

    private function runSeeder(string $class, OutputInterface $output): void
    {
        if (class_exists($class)) {
            $seeder = new $class();
            $seeder->run();
            $output->writeln("<info>Seeded: {$class}</info>");
        } else {
            $output->writeln("<error>Seeder class not found: {$class}</error>");
        }
    }

    private function runAllSeeders(?string $module, OutputInterface $output): void
    {
        if ($module) {
            $this->runSeedersInDirectory("modules/{$module}/database/seeders", $output);
        } else {
            // Seeders globaux
            $this->runSeedersInDirectory('database/seeders', $output);

            // Seeders des modules
            $modulesPath = 'modules';
            if (is_dir($modulesPath)) {
                $modules = scandir($modulesPath);
                foreach ($modules as $moduleDir) {
                    if ($moduleDir !== '.' && $moduleDir !== '..' && is_dir("{$modulesPath}/{$moduleDir}")) {
                        $this->runSeedersInDirectory("modules/{$moduleDir}/database/seeders", $output);
                    }
                }
            }
        }
    }

    private function runSeedersInDirectory(string $directory, OutputInterface $output): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob("{$directory}/*.php");
        
        foreach ($files as $file) {
            require_once $file;
            $className = $this->getSeederClassName($file);
            
            if ($className && class_exists($className)) {
                $seeder = new $className();
                $seeder->run();
                $output->writeln("<info>Seeded: {$className}</info>");
            }
        }
    }

    private function getSeederClassName(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class\s+(\w+)/', $content, $matches);
        return $matches[1] ?? '';
    }
}