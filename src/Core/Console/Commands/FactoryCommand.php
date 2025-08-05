<?php

namespace IronFlow\Core\Console\Commands;

use IronFlow\Core\Database\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FactoryCommand extends BaseCommand
{
    protected static $defaultName = 'factory';

    protected function configure(): void
    {
        $this->addArgument('factory', InputArgument::REQUIRED, 'Factory class name');
        $this->addArgument('count', InputArgument::OPTIONAL, 'Number of records', 1);
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $factoryName = $input->getArgument('factory');
        $count = (int)$input->getArgument('count');
        $factoryClass = "Factories\\$factoryName";
        if (!class_exists($factoryClass)) {
            $output->writeln("<error>Factory not found:</error> $factoryClass");
            return Command::FAILURE;
        }
        $factory = new $factoryClass();
        for ($i = 0; $i < $count; $i++) {
            $factory->create();
        }
        $output->writeln("<info>Created $count record(s) with $factoryClass</info>");
        return Command::SUCCESS;
    }
}
