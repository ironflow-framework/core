<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Database;

use Exception;
use IronFlow\Database\Connection;
use IronFlow\Database\Migrations\Migrator;
use IronFlow\Database\Seeders\DatabaseSeeder;
use IronFlow\Database\Seeders\SeederManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\Output;

/**
 * Commande de seeding améliorée
 */
class DbSeedCommand extends Command
{
   protected static $defaultName = 'db:seed';
   protected string $description = 'Exécute les seeders de la base de données';

   protected function configure(): void
   {
      $this
         ->setDescription($this->description)
         ->addArgument('seeder', InputArgument::OPTIONAL, 'Seeder spécifique à exécuter')
         ->addOption('class', 'c', InputOption::VALUE_REQUIRED, 'Classe de seeder à exécuter')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force l\'exécution même si la DB n\'est pas à jour')
         ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Désactive la barre de progression');
   }

   public function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      try {
         $io->title('🌱 Database Seeding');

         // Vérification des migrations si pas forcé
         if (!$input->getOption('force')) {
            $this->checkMigrations($io);
         }

         $seederClass = $input->getOption('class') ?? $input->getArgument('seeder');

         if ($seederClass) {
            return $this->runSpecificSeeder($seederClass, $io, $output, $input);
         } else {
            return $this->runAllSeeders($io, $output, $input);
         }
      } catch (Exception $e) {
         $io->error('Erreur lors du seeding :');
         $io->error($e->getMessage());

         if ($output->isVerbose()) {
            $io->section('Stack trace :');
            $io->text($e->getTraceAsString());
         }

         return Command::FAILURE;
      }
   }

   /**
    * Vérifie l'état des migrations
    */
   protected function checkMigrations(SymfonyStyle $io): void
   {
      $migrator = new Migrator(
         Connection::getInstance()->getConnection(),
         database_path('migrations')
      );

      if ($migrator->isDirty()) {
         $io->warning('La base de données a des migrations en attente.');
         $io->note('Exécutez `php artisan migrate` ou utilisez --force pour ignorer cette vérification.');
         throw new Exception('Migrations en attente détectées');
      }

      $io->success('Base de données à jour');
   }

   /**
    * Exécute un seeder spécifique
    */
   protected function runSpecificSeeder(string $seederClass, SymfonyStyle $io, OutputInterface $output, InputInterface $input): int
   {
      $io->section("Exécution du seeder : {$seederClass}");

      if (!class_exists($seederClass)) {
         $io->error("La classe {$seederClass} n'existe pas");
         return Command::FAILURE;
      }

      $connection = Connection::getInstance()->getConnection();
      $seeder = new $seederClass($connection);

      $showProgress = !$input->getOption('no-progress');
      $progressBar = null;

      if ($showProgress) {
         $progressBar = new ProgressBar($output, 1);
         $progressBar->start();
      }

      $seeder->execute(function ($seederName) use ($io, $progressBar) {
         if ($progressBar) {
            $progressBar->advance();
         }
         $io->writeln("  <info>✓</info> " . class_basename($seederName));
      });

      if ($progressBar) {
         $progressBar->finish();
         $io->newLine(2);
      }

      $io->success("Seeder {$seederClass} exécuté avec succès !");
      return Command::SUCCESS;
   }

   /**
    * Exécute tous les seeders
    */
   protected function runAllSeeders(SymfonyStyle $io, OutputInterface $output, InputInterface $input): int
   {
      $io->section('Exécution de tous les seeders');

      $connection = Connection::getInstance()->getConnection();
      $seeder = new SeederManager($connection);

      $showProgress = !$input->getOption('no-progress');
      $progressBar = null;
      $seedersCount = 0;

      if ($showProgress) {
         // Estimation du nombre de seeders (peut être amélioré)
         $progressBar = new ProgressBar($output, 10);
         $progressBar->start();
      }

      $seeder->run(function ($seederName) use ($io, $progressBar, &$seedersCount) {
         if ($progressBar) {
            $progressBar->advance();
         }
         $seedersCount++;
         $io->writeln("  <info>✓</info> " . class_basename($seederName));
      });

      if ($progressBar) {
         $progressBar->finish();
         $io->newLine(2);
      }

      $io->success("Tous les seeders ont été exécutés avec succès ! ({$seedersCount} seeders)");
      return Command::SUCCESS;
   }
}
