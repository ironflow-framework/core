<?php

declare(strict_types=1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KeyGenerateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('key:generate')
            ->setDescription('Génère une nouvelle clé d’application dans le fichier .env');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $key = bin2hex(random_bytes(32));
        $envPath = dirname(__DIR__ , 7) . '/.env';

        if (!file_exists($envPath)) {
            $output->writeln($envPath);
            $output->writeln('<error>Fichier .env introuvable.</error>');
            return Command::FAILURE;
        }

        $envContent = file_get_contents($envPath);
        $envContent = preg_replace('/^APP_KEY=.*$/m', '', $envContent);
        $envContent .= "\nAPP_KEY=$key\n";

        file_put_contents($envPath, $envContent);

        $output->writeln("<info>Clé générée avec succès :</info> <comment>$key</comment>");
        return Command::SUCCESS;
    }
}

