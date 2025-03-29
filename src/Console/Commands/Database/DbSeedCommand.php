<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Database\Connection;
use IronFlow\Database\Seeder\SeederManager;

/**
 * Commande pour exécuter les seeders
 */
class DbSeedCommand extends Command
{
   protected static $defaultName = 'db:seed';
   protected static $defaultDescription = 'Exécute les seeders de base de données';

   /**
    * Configure la commande
    *
    * @return void
    */
   protected function configure(): void
   {
      $this
         ->addArgument('seeder', InputArgument::OPTIONAL, 'Nom du seeder à exécuter (sans le suffix "Seeder")');
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
      $io->title('Exécution des seeders');

      $connection = Connection::getInstance()->getConnection();
      $manager = new SeederManager($connection);

      $specificSeeder = $input->getArgument('seeder');

      try {
         if ($specificSeeder) {
            $io->section("Exécution du seeder: {$specificSeeder}");
            $manager->runSpecific($specificSeeder);
            $io->success("Seeder {$specificSeeder} exécuté avec succès");
         } else {
            $io->section('Exécution de tous les seeders');
            $manager->run();
            $io->success('Tous les seeders ont été exécutés avec succès');
         }
      } catch (\Exception $e) {
         $io->error('Erreur lors de l\'exécution des seeders: ' . $e->getMessage());
         return Command::FAILURE;
      }

      return Command::SUCCESS;
   }
}
