<?php

declare(strict_types=1);

namespace IronFlow\Core\Console;

use IronFlow\Core\Container\Container;

use IronFlow\Core\Console\Commands\FactoryCommand;
use IronFlow\Core\Console\Commands\KeyGenerateCommand;
use IronFlow\Core\Console\Commands\MakeControllerCommand;
use IronFlow\Core\Console\Commands\MakeMigrationCommand;
use IronFlow\Core\Console\Commands\MakeModelCommand;
use IronFlow\Core\Console\Commands\MakeModuleCommand;
use IronFlow\Core\Console\Commands\MakeSeederCommand;
use IronFlow\Core\Console\Commands\MigrateCommand;
use IronFlow\Core\Console\Commands\SeedCommand;
use IronFlow\Core\Console\Commands\ServeCommand;

use Symfony\Component\Console\Application;


/**
 * Console Kernel - Gestionnaire de commandes artisanales
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
            KeyGenerateCommand::class,
            FactoryCommand::class,
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
