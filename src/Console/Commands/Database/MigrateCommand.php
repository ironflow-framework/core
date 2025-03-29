<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Database\Connection;
use IronFlow\Database\Migrations\Migrator;
use PDO;

/**
 * Commande pour exécuter les migrations
 */
class MigrateCommand extends Command
{
   protected static $defaultName = 'migrate';
   protected static $defaultDescription = 'Exécute les migrations de base de données';

   /**
    * Configure la commande
    *
    * @return void
    */
   protected function configure(): void
   {
      $this
         ->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Annule la dernière migration')
         ->addOption('reset', null, InputOption::VALUE_NONE, 'Annule toutes les migrations')
         ->addOption('refresh', null, InputOption::VALUE_NONE, 'Annule toutes les migrations et les réexécute')
         ->addOption('fresh', 'f', InputOption::VALUE_NONE, 'Supprime toutes les tables et relance les migrations')
         ->addOption('seed', 's', InputOption::VALUE_NONE, 'Exécute les seeders après les migrations')
         ->addOption('steps', null, InputOption::VALUE_REQUIRED, 'Nombre de migrations à annuler', 1);
   }

   /**
    * Exécute la commande
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return int
    */
   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $io->title('Migrations de base de données');

      $connection = Connection::getInstance()->getConnection();
      $migrator = new Migrator($connection, database_path('migrations'));

      if ($input->getOption('fresh')) {
         $io->section('Suppression de toutes les tables');
         $this->dropAllTables($connection);
         $io->success('Toutes les tables ont été supprimées');
      }

      if ($input->getOption('reset')) {
         $io->section('Annulation de toutes les migrations');
         $this->reset($migrator, $io);
      } elseif ($input->getOption('refresh')) {
         $io->section('Rafraîchissement des migrations');
         $this->refresh($migrator, $io);
      } elseif ($input->getOption('rollback')) {
         $io->section('Annulation des migrations');
         $steps = (int) $input->getOption('steps');
         $this->rollback($migrator, $io, $steps);
      } else {
         $io->section('Exécution des migrations');
         $this->migrate($migrator, $io);
      }

      if ($input->getOption('seed')) {
         $io->section('Exécution des seeders');
         $this->seed($connection, $io);
      }

      return Command::SUCCESS;
   }

   /**
    * Exécute les migrations
    *
    * @param Migrator $migrator
    * @param SymfonyStyle $io
    * @return void
    */
   private function migrate(Migrator $migrator, SymfonyStyle $io): void
   {
      $migrations = $migrator->migrate();

      if (empty($migrations)) {
         $io->info('Aucune migration à exécuter');
         return;
      }

      foreach ($migrations as $migration) {
         $io->text("Migration: {$migration}");
      }

      $io->success(count($migrations) . ' migration(s) exécutée(s) avec succès');
   }

   /**
    * Annule les migrations
    *
    * @param Migrator $migrator
    * @param SymfonyStyle $io
    * @param int $steps
    * @return void
    */
   private function rollback(Migrator $migrator, SymfonyStyle $io, int $steps = 1): void
   {
      $migrations = $migrator->rollback($steps);

      if (empty($migrations)) {
         $io->warning('Aucune migration à annuler');
         return;
      }

      foreach ($migrations as $migration) {
         $io->text("Annulation: {$migration}");
      }

      $io->success(count($migrations) . ' migration(s) annulée(s)');
   }

   /**
    * Annule toutes les migrations
    *
    * @param Migrator $migrator
    * @param SymfonyStyle $io
    * @return void
    */
   private function reset(Migrator $migrator, SymfonyStyle $io): void
   {
      $migrations = $migrator->reset();

      if (empty($migrations)) {
         $io->warning('Aucune migration à annuler');
         return;
      }

      foreach ($migrations as $migration) {
         $io->text("Annulation: {$migration}");
      }

      $io->success(count($migrations) . ' migration(s) annulée(s)');
   }

   /**
    * Annule toutes les migrations et les réexécute
    *
    * @param Migrator $migrator
    * @param SymfonyStyle $io
    * @return void
    */
   private function refresh(Migrator $migrator, SymfonyStyle $io): void
   {
      $io->section('Annulation de toutes les migrations');
      $this->reset($migrator, $io);

      $io->section('Exécution des migrations');
      $this->migrate($migrator, $io);
   }

   /**
    * Exécute les seeders
    *
    * @param PDO $connection
    * @param SymfonyStyle $io
    * @return void
    */
   private function seed(PDO $connection, SymfonyStyle $io): void
   {
      try {
         $seederManager = new \IronFlow\Database\Seeder\SeederManager($connection);
         $seederManager->run();
         $io->success('Seeders exécutés avec succès');
      } catch (\Exception $e) {
         $io->error('Erreur lors de l\'exécution des seeders: ' . $e->getMessage());
      }
   }

   /**
    * Supprime toutes les tables
    *
    * @param PDO $connection
    * @return void
    */
   private function dropAllTables(PDO $connection): void
   {
      $driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
      $tables = [];

      switch ($driver) {
         case 'mysql':
            $stmt = $connection->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $connection->exec('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tables as $table) {
               $connection->exec("DROP TABLE `{$table}`");
            }
            $connection->exec('SET FOREIGN_KEY_CHECKS=1');
            break;

         case 'sqlite':
            $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
               $connection->exec("DROP TABLE \"{$table}\"");
            }
            break;

         case 'pgsql':
            $stmt = $connection->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
               $connection->exec("DROP TABLE \"{$table}\" CASCADE");
            }
            break;
      }
   }
}
