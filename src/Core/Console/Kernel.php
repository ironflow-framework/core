<?php

declare(strict_types=1);

namespace IronFlow\Core\Console;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Console\Commands\{
    FactoryCommand,
    KeyGenerateCommand,
    MakeControllerCommand,
    MakeMigrationCommand,
    MakeModelCommand,
    MakeModuleCommand,
    MakeSeederCommand,
    MigrateCommand,
    SeedCommand,
    ServeCommand
};
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Throwable;

/**
 * Console Kernel - Gestionnaire de commandes artisanales
 * 
 * Gère l'enregistrement et l'exécution des commandes CLI du framework IronFlow.
 * Fournit un système de logging intégré et une gestion d'erreurs robuste.
 */
class Kernel
{
    private Application $console;
    private Container $container;
    private Logger $logger;
    private array $commands = [];
    private array $config;

    /**
     * @param Container $container Conteneur de dépendances
     * @param array $config Configuration du kernel console
     */
    public function __construct(Container $container, array $config = [])
    {
        $this->container = $container;
        $this->config = array_merge($this->getDefaultConfig(), $config);
        
        $this->initializeLogger();
        $this->initializeConsole();
        $this->registerCoreCommands();
    }

    /**
     * Configuration par défaut du kernel
     */
    private function getDefaultConfig(): array
    {
        return [
            'name' => 'IronFlow Forge',
            'version' => '1.0.0',
            'log_path' => 'storage/logs/console.log',
            'log_level' => Logger::INFO,
            'max_log_files' => 7,
            'timezone' => 'UTC'
        ];
    }

    /**
     * Initialise le système de logging
     */
    private function initializeLogger(): void
    {
        $this->logger = new Logger('ironflow.console');
        
        // Handler pour fichiers rotatifs
        $fileHandler = new RotatingFileHandler(
            $this->config['log_path'],
            $this->config['max_log_files'],
            $this->config['log_level']
        );
        
        // Formatter personnalisé
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );
        $fileHandler->setFormatter($formatter);
        
        $this->logger->pushHandler($fileHandler);
        
        // Enregistrer le logger dans le container
        $this->container->singleton(Logger::class, fn() => $this->logger);
    }

    /**
     * Initialise l'application console
     */
    private function initializeConsole(): void
    {
        $this->console = new Application(
            $this->config['name'],
            $this->config['version']
        );
        
        // Configuration de l'affichage
        $this->console->setCatchExceptions(false);
        $this->console->setAutoExit(false);
        
        // Enregistrer l'application dans le container
        $this->container->singleton(Application::class, fn() => $this->console);
    }

    /**
     * Enregistre les commandes de base du framework
     */
    private function registerCoreCommands(): void
    {
        $coreCommands = [
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

        foreach ($coreCommands as $commandClass) {
            $this->registerCommand($commandClass);
        }

        $this->logger->info('Core commands registered', [
            'count' => count($coreCommands),
            'commands' => $coreCommands
        ]);
    }

    /**
     * Enregistre une commande
     */
    private function registerCommand(string $commandClass): void
    {
        try {
            if (!class_exists($commandClass)) {
                throw new \InvalidArgumentException("Command class {$commandClass} does not exist");
            }

            if (!is_subclass_of($commandClass, Command::class)) {
                throw new \InvalidArgumentException("Command class {$commandClass} must extend Symfony Command");
            }

            $command = $this->container->make($commandClass);
            $this->console->add($command);
            $this->commands[] = $commandClass;

            $this->logger->debug('Command registered', [
                'class' => $commandClass,
                'name' => $command->getName()
            ]);

        } catch (Throwable $e) {
            $this->logger->error('Failed to register command', [
                'class' => $commandClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Ajoute une commande personnalisée
     */
    public function addCommand(string $commandClass): self
    {
        $this->registerCommand($commandClass);
        return $this;
    }

    /**
     * Ajoute plusieurs commandes
     */
    public function addCommands(array $commandClasses): self
    {
        foreach ($commandClasses as $commandClass) {
            $this->addCommand($commandClass);
        }
        return $this;
    }

    /**
     * Obtient la liste des commandes enregistrées
     */
    public function getRegisteredCommands(): array
    {
        return $this->commands;
    }

    /**
     * Obtient l'application console
     */
    public function getConsole(): Application
    {
        return $this->console;
    }

    /**
     * Obtient le logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Lance l'application console avec gestion d'erreurs
     */
    public function handle(?ArgvInput $input = null, ?ConsoleOutput $output = null): int
    {
        $input = $input ?? new ArgvInput();
        $output = $output ?? new ConsoleOutput();

        $startTime = microtime(true);
        $commandName = $this->getCommandName($input);

        $this->logger->info('Console command started', [
            'command' => $commandName,
            'arguments' => $input->getArguments(),
            'options' => $input->getOptions(),
            'user' => get_current_user(),
            'cwd' => getcwd()
        ]);

        try {
            $exitCode = $this->console->run($input, $output);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('Console command completed', [
                'command' => $commandName,
                'exit_code' => $exitCode,
                'execution_time_ms' => $executionTime,
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true))
            ]);

            return $exitCode;

        } catch (Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('Console command failed', [
                'command' => $commandName,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'execution_time_ms' => $executionTime,
                'trace' => $e->getTraceAsString()
            ]);

            $output->writeln("<error>Command failed: {$e->getMessage()}</error>");
            
            if ($output->isVerbose()) {
                $output->writeln("<comment>{$e->getTraceAsString()}</comment>");
            }

            return Command::FAILURE;
        }
    }

    /**
     * Extrait le nom de la commande depuis l'input
     */
    private function getCommandName(ArgvInput $input): string
    {
        $arguments = $input->getArguments();
        return isset($arguments['command']) ? $arguments['command'] : 'unknown';
    }

    /**
     * Formate les bytes en format lisible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Définit le timezone pour les logs
     */
    public function setTimezone(string $timezone): self
    {
        date_default_timezone_set($timezone);
        $this->config['timezone'] = $timezone;
        return $this;
    }

    /**
     * Active le mode debug
     */
    public function enableDebug(): self
    {
        $this->logger->pushHandler(
            new StreamHandler('php://stderr', Logger::DEBUG)
        );
        return $this;
    }
}