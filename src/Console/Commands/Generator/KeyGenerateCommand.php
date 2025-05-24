<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KeyGenerateCommand extends Command
{
    protected static $defaultName = 'key:generate';
    protected static $defaultDescription = 'Génère une nouvelle clé d\'application';

    protected function configure(): void
    {
        $this
            ->addOption('show', null, InputOption::VALUE_NONE, 'Affiche la clé générée sans la sauvegarder')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force la génération même si une clé existe déjà');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $key = $this->generateRandomKey();

        if ($input->getOption('show')) {
            $io->info("Clé générée : {$key}");
            return Command::SUCCESS;
        }

        $envPath = base_path('.env');

        if (!Filesystem::exists($envPath)) {
            $io->error('Le fichier .env n\'existe pas. Créez-le d\'abord.');
            return Command::FAILURE;
        }

        $envContent = Filesystem::get($envPath);

        // Vérifier si une clé existe déjà
        if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)) {
            $existingKey = trim($matches[1]);
            if (!empty($existingKey) && !$input->getOption('force')) {
                $io->warning('Une clé d\'application existe déjà.');
                if (!$io->confirm('Voulez-vous la remplacer ?', false)) {
                    $io->info('Génération de clé annulée.');
                    return Command::SUCCESS;
                }
            }
        }

        // Mettre à jour ou ajouter la clé dans le fichier .env
        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
            $newContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $envContent);
        } else {
            $newContent = $envContent . "\nAPP_KEY={$key}";
        }

        Filesystem::put($envPath, $newContent);

        $io->success('Clé d\'application générée avec succès !');
        $io->info("Nouvelle clé : {$key}");

        return Command::SUCCESS;
    }

    protected function generateRandomKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}
