<?php

declare(strict_types=1);

namespace IronFlow\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Noyau de la console IronFlow
 * 
 * Gère l'enregistrement et l'exécution des commandes de la console.
 */
class Kernel extends ConsoleApplication
{
   /**
    * Liste des commandes à enregistrer
    *
    * @var array
    */
   protected array $commands = [];

   /**
    * Constructeur
    *
    * @param string $name Nom de l'application
    * @param string $version Version de l'application
    */
   public function __construct(string $name = 'IronFlow', string $version = '1.0.0')
   {
      parent::__construct($name, $version);
      $this->registerDefaultCommands();
   }

   /**
    * Ajoute une commande à l'application
    *
    * @param Command $command Instance de la commande
    * @return Command
    */
   public function addCommand(Command $command): Command
   {
      return parent::add($command);
   }

   /**
    * Ajoute une commande dans la liste des commandes à enregistrer
    *
    * @param string $command Classe de la commande
    * @return self
    */
   public function command(string $command): self
   {
      $this->commands[] = $command;
      return $this;
   }

   /**
    * Enregistre les commandes par défaut
    *
    * @return void
    */
   protected function registerDefaultCommands(): void
   {
      $defaultCommands = [
         // Commandes de base
         \IronFlow\Console\Commands\ServeCommand::class,
         \IronFlow\Console\Commands\SetupCommand::class,

         // Commandes de base de données
         \IronFlow\Console\Commands\Database\MigrateCommand::class,
         \IronFlow\Console\Commands\Database\DbSeedCommand::class,

         // Commandes de génération
         \IronFlow\Console\Commands\Generator\MakeMigrationCommand::class,
         \IronFlow\Console\Commands\Generator\MakeSeederCommand::class,
         \IronFlow\Console\Commands\Generator\MakeControllerCommand::class,
         \IronFlow\Console\Commands\Generator\MakeModelCommand::class,
         \IronFlow\Console\Commands\Generator\MakeMiddlewareCommand::class,
         \IronFlow\Console\Commands\Generator\MakeServiceCommand::class,
         \IronFlow\Console\Commands\Generator\MakeFactoryCommand::class,
         \IronFlow\Console\Commands\Generator\MakeTestCommand::class,
         \IronFlow\Console\Commands\Generator\MakeValidatorCommand::class,
         \IronFlow\Console\Commands\Generator\MakeComponentCommand::class,
         \IronFlow\Console\Commands\Generator\MakeFormCommand::class,
         \IronFlow\Console\Commands\Generator\MakeTranslationCommand::class,
         \IronFlow\Console\Commands\Generator\ScaffoldCommand::class,

         // Commandes d'authentification
         \IronFlow\Console\Commands\Auth\AuthInstallCommand::class,

         // Commandes CraftPanel - utilisent le namespace Generator
         \IronFlow\Console\Commands\CraftPanel\CraftPanelInstallCommand::class,
         \IronFlow\Console\Commands\CraftPanel\MakeAdminCommand::class,
         \IronFlow\Console\Commands\CraftPanel\RegisterModelCommand::class,

         // Commandes Channel
         \IronFlow\Console\Commands\Channel\ChannelInitCommand::class,

         // Commandes Payment
         \IronFlow\Console\Commands\Payment\PaymentInstallCommand::class,

         // Commandes Vibe (Media)
         \IronFlow\Console\Commands\Vibe\CreateMediaTableCommand::class,
      ];

      foreach ($defaultCommands as $command) {
         $this->command($command);
      }

      $this->registerCommands();
   }

   /**
    * Enregistre toutes les commandes définies
    *
    * @return void
    */
   public function registerCommands(): void
   {
      foreach ($this->commands as $command) {
         if (class_exists($command)) {
            $this->addCommand(new $command());
         }
      }
   }

   /**
    * Exécute la console
    *
    * @param InputInterface|null $input
    * @param OutputInterface|null $output
    * @return int
    */
   public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int
   {
      $this->registerCommands();
      return parent::run($input, $output);
   }
}
