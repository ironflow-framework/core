<?php

declare(strict_types=1);

namespace IronFlow\Core\CLI;

use IronFlow\Core\Container\Container;
use IronFlow\Core\CLI\Commands\MakeControllerCommand;
use IronFlow\Core\CLI\Commands\MakeMigrationCommand;
use IronFlow\Core\CLI\Commands\MakeModelCommand;
use IronFlow\Core\CLI\Commands\MakeModuleCommand;
use IronFlow\Core\CLI\Commands\MakeSeederCommand;
use IronFlow\Core\CLI\Commands\MigrateCommand;
use IronFlow\Core\CLI\Commands\SeedCommand;
use IronFlow\Core\CLI\Commands\ServeCommand;

use Symfony\Component\Console\Application;


/**
 * CLI Kernel - Gestionnaire de commandes artisanales
 */
class Kernel
{
    private Application $console;
    private Container $container;
    private array $commands = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->console = new Application('IronFlow Forge', '1.0.0');
        $this->registerCoreCommands();
    }

    /**
     * Enregistre les commandes de base du framework
     */
    private function registerCoreCommands(): void
    {
        $this->commands = [
            MakeModuleCommand::class,
            MakeControllerCommand::class,
            MakeModelCommand::class,
            MakeMigrationCommand::class,
            MakeSeederCommand::class,
            MigrateCommand::class,
            SeedCommand::class,
            ServeCommand::class,
        ];

        foreach ($this->commands as $commandClass) {
            $this->console->add($this->container->make($commandClass));
        }
    }

    /**
     * Ajoute une commande personnalisée
     */
    public function addCommand(string $commandClass): void
    {
        $this->commands[] = $commandClass;
        $this->console->add($this->container->make($commandClass));
    }

    /**
     * Lance l'application console
     */
    public function handle(): int
    {
        return $this->console->run();
    }
}