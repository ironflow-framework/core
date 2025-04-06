<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Database;

use App\Database\Seeders\DatabaseSeeder;
use IronFlow\Database\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DbSeedCommand extends Command
{
   
    protected static $defaultName  = 'db:seed';
    protected string $description = 'Exécute les seeders de la base de données';

   /**
    * Exécute la commande
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return int
    */
   public function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $io->info('Exécution des seeders...');

      try {
         $seeder = new DatabaseSeeder(Connection::getInstance());
         $seeder->run();

         $io->success('Seeders exécutés avec succès !');
         return 0;
      } catch (\Exception $e) {
         $io->error('Erreur lors de l\'exécution des seeders : ' . $e->getMessage());
         return 1;
      }
   }
}
