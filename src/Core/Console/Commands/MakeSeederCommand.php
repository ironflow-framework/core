<?php

declare(strict_types= 1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Commande: make:seeder
 */
class MakeSeederCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:seeder')
             ->setDescription('Create a new seeder')
             ->addArgument('name', InputArgument::REQUIRED, 'Seeder name')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = $input->getOption('module');

        if ($module) {
            $namespace = "App\\Modules\\{$module}\\Database\\Seeders";
            $path = "modules/{$module}/database/seeders/{$name}.php";
        } else {
            $namespace = "App\\Database\\Seeders";
            $path = "database/seeders/{$name}.php";
        }

        $content = $this->generateFromTemplate('Seeder', [
            'CLASS_NAME' => $name,
            'NAMESPACE' => $namespace
        ]);

        $this->writeFile($path, $content, $output);

        $output->writeln("<comment>Seeder {$name} created successfully!</comment>");
        return Command::SUCCESS;
    }
}
