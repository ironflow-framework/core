<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ServeCommand extends Command
{
   protected static $defaultName = 'serve';
   protected static $defaultDescription = 'Lance le serveur de développement';

   protected function configure(): void
   {
      $this
         ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'L\'hôte du serveur', 'localhost')
         ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Le port du serveur', 8000)
         ->addOption('no-logs', null, InputOption::VALUE_NONE, 'Désactive l\'affichage des logs');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $host = $input->getOption('host');
      $port = $input->getOption('port');
      $showLogs = !$input->getOption('no-logs');

      $io->section('Serveur de développement IronFlow');
      $io->text("Serveur démarré sur http://{$host}:{$port}");
      $io->text('Appuyez sur Ctrl+C pour arrêter le serveur.');

      // Démarrer le serveur PHP
      $serverProcess = new Process(['php', '-S', "{$host}:{$port}", '-t', 'public']);
      $serverProcess->start();

      // Démarrer le processus de surveillance des logs si activé
      if ($showLogs) {
         $logProcess = new Process(['tail', '-f', 'storage/logs/ironflow.log']);
         $logProcess->start();

         $io->text('Surveillance des logs activée...');
      }

      try {
         // Afficher la sortie du serveur en temps réel
         while ($serverProcess->isRunning()) {
            $output->write($serverProcess->getIncrementalOutput());
            $output->write($serverProcess->getIncrementalErrorOutput());

            if ($showLogs) {
               $output->write($logProcess->getIncrementalOutput());
            }

            usleep(100000); // 100ms de délai pour éviter une utilisation excessive du CPU
         }
      } catch (ProcessFailedException $e) {
         $io->error('Le serveur a échoué : ' . $e->getMessage());
         return Command::FAILURE;
      }

      return Command::SUCCESS;
   }
}
