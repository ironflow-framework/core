<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: make:controller
 */
class MakeControllerCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:controller')
             ->setDescription('Create a new controller')
             ->addArgument('name', InputArgument::REQUIRED, 'Controller name')
             ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = $input->getOption('module');

        if ($module) {
            $namespace = "App\\Modules\\{$module}\\Controllers";
            $path = "modules/{$module}/Controllers/{$name}.php";
        } else {
            $namespace = "App\\Controllers";
            $path = "app/Controllers/{$name}.php";
        }

        $content = $this->generateFromTemplate('Controller', [
            'CLASS_NAME' => $name,
            'NAMESPACE' => $namespace
        ]);

        $this->writeFile($path, $content, $output);

        $output->writeln("<comment>Controller {$name} created successfully!</comment>");
        return Command::SUCCESS;
    }
}
