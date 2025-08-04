<?php

declare(strict_types= 1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande: serve
 */
class ServeCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('serve')
             ->setDescription('Start the development server')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host address', 'localhost')
             ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port number', '8000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');

        $output->writeln("<info>Starting IronFlow development server...</info>");
        $output->writeln("<comment>Server running at http://{$host}:{$port}</comment>");
        $output->writeln("<comment>Press Ctrl+C to stop</comment>");

        $documentRoot = realpath('public');
        $command = "php -S {$host}:{$port} -t {$documentRoot}";

        passthru($command);

        return Command::SUCCESS;
    }
}