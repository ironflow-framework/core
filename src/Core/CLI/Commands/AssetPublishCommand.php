<?php

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssetPublishCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('assets:publish')
            ->setDescription('Publie les assets des modules dans le dossier public');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $modulesPath = base_path('app/');
        $publicPath = base_path('public/modules');

        $modules = glob($modulesPath . '*/Resources/assets', GLOB_ONLYDIR);

        if (empty($modules)) {
            $output->writeln('<comment>Aucun asset à publier.</comment>');
            return Command::SUCCESS;
        }

        foreach ($modules as $assetPath) {
            $moduleName = basename(dirname(dirname($assetPath)));
            $destination = "$publicPath/$moduleName";

            $filesystem->mkdir($destination);
            $filesystem->mirror($assetPath, $destination);

            $output->writeln("<info>Assets publiés pour :</info> <comment>$moduleName</comment>");
        }

        return Command::SUCCESS;
    }
}