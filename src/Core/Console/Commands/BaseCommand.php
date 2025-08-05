<?php

declare(strict_types=1);

namespace IronFlow\Core\Console\Commands;

use IronFlow\Core\Container\Container;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

/**
 * Commande de base pour toutes les commandes IronFlow
 * 
 * Fournit des utilitaires communs et une interface cohérente
 * pour toutes les commandes du framework.
 */
abstract class BaseCommand extends Command
{
    protected Container $container;
    protected Logger $logger;
    protected SymfonyStyle $io;
    protected array $config;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->make(Logger::class);
        $this->config = $this->getDefaultConfig();
        
        parent::__construct();
    }

    /**
     * Configuration par défaut
     */
    protected function getDefaultConfig(): array
    {
        return [
            'templates_path' => __DIR__ . '/Templates',
            'base_namespace' => 'App',
            'app_path' => 'app',
            'force_overwrite' => false,
            'backup_existing' => true
        ];
    }

    /**
     * Initialise l'interface utilisateur
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        
        $this->logger->debug('Command initialized', [
            'command' => $this->getName(),
            'arguments' => $input->getArguments(),
            'options' => $input->getOptions()
        ]);
    }

    /**
     * Exécution avec gestion d'erreurs et logging
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->handle($input, $output);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('Command executed successfully', [
                'command' => $this->getName(),
                'execution_time_ms' => $executionTime,
                'result' => $result
            ]);
            
            return $result;
            
        } catch (Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('Command execution failed', [
                'command' => $this->getName(),
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->io->error("Command failed: {$e->getMessage()}");
            
            if ($output->isVerbose()) {
                $this->io->text($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }

    /**
     * Méthode principale à implémenter par les commandes enfants
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output): int;

    /**
     * Crée un répertoire avec gestion des permissions et logging
     */
    protected function ensureDirectoryExists(string $path, int $permissions = 0755): bool
    {
        if (is_dir($path)) {
            return true;
        }

        try {
            $result = mkdir($path, $permissions, true);
            
            if ($result) {
                $this->logger->debug('Directory created', ['path' => $path]);
                return true;
            }
            
            $this->logger->warning('Failed to create directory', ['path' => $path]);
            return false;
            
        } catch (Throwable $e) {
            $this->logger->error('Directory creation error', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Génère le contenu d'un fichier à partir d'un template avec validation
     */
    protected function generateFromTemplate(string $template, array $replacements = []): string
    {
        $content = $this->getTemplate($template);
        
        // Validation des remplacements requis
        $requiredPlaceholders = $this->extractPlaceholders($content);
        $missing = array_diff($requiredPlaceholders, array_keys($replacements));
        
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                "Missing required template replacements: " . implode(', ', $missing)
            );
        }
        
        // Remplacement sécurisé
        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{$search}}", $this->sanitizeReplacement($replace), $content);
        }

        return $content;
    }

    /**
     * Extrait les placeholders d'un template
     */
    private function extractPlaceholders(string $content): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Sanitise une valeur de remplacement
     */
    private function sanitizeReplacement(string $value): string
    {
        // Échappe les caractères dangereux
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtient le contenu d'un template avec cache
     */
    protected function getTemplate(string $name): string
    {
        static $templateCache = [];
        
        if (isset($templateCache[$name])) {
            return $templateCache[$name];
        }
        
        $templatePath = $this->config['templates_path'] . "/{$name}.php.template";
        
        if (!file_exists($templatePath)) {
            throw new \InvalidArgumentException("Template '{$name}' not found at: {$templatePath}");
        }

        $content = file_get_contents($templatePath);
        
        if ($content === false) {
            throw new \RuntimeException("Failed to read template: {$templatePath}");
        }
        
        $templateCache[$name] = $content;
        
        $this->logger->debug('Template loaded', [
            'name' => $name,
            'path' => $templatePath,
            'size' => strlen($content)
        ]);
        
        return $content;
    }

    /**
     * Écrit un fichier avec gestion des conflits et backup
     */
    protected function writeFile(string $path, string $content, bool $force = false): bool
    {
        $fileExists = file_exists($path);
        
        if ($fileExists && !$force && !$this->config['force_overwrite']) {
            if (!$this->confirmOverwrite($path)) {
                $this->io->note('File creation cancelled');
                return false;
            }
        }
        
        // Backup du fichier existant
        if ($fileExists && $this->config['backup_existing']) {
            $this->backupFile($path);
        }
        
        $this->ensureDirectoryExists(dirname($path));
        
        try {
            $result = file_put_contents($path, $content);
            
            if ($result === false) {
                throw new \RuntimeException("Failed to write file: {$path}");
            }
            
            $this->logger->info('File written', [
                'path' => $path,
                'size' => $result,
                'overwritten' => $fileExists
            ]);
            
            $this->io->success($fileExists ? "Updated: {$path}" : "Created: {$path}");
            return true;
            
        } catch (Throwable $e) {
            $this->logger->error('File writing failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            
            $this->io->error("Failed to write file: {$path}");
            return false;
        }
    }

    /**
     * Demande confirmation pour écraser un fichier
     */
    private function confirmOverwrite(string $path): bool
    {
        $question = new ConfirmationQuestion(
            "File '{$path}' already exists. Overwrite? [y/N] ",
            false
        );
        
        return $this->getHelper('question')->ask($this->io->getInput(), $this->io, $question);
    }

    /**
     * Crée un backup d'un fichier existant
     */
    private function backupFile(string $path): bool
    {
        $backupPath = $path . '.backup.' . date('Y-m-d_H-i-s');
        
        try {
            $result = copy($path, $backupPath);
            
            if ($result) {
                $this->logger->debug('File backed up', [
                    'original' => $path,
                    'backup' => $backupPath
                ]);
                
                $this->io->note("Backup created: {$backupPath}");
            }
            
            return $result;
            
        } catch (Throwable $e) {
            $this->logger->warning('Backup failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Valide un nom de classe
     */
    protected function validateClassName(string $name): string
    {
        if (!preg_match('/^[A-Z][a-zA-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException(
                "Invalid class name '{$name}'. Must start with uppercase letter and contain only alphanumeric characters and underscores."
            );
        }
        
        return $name;
    }

    /**
     * Convertit un nom en format CamelCase
     */
    protected function toCamelCase(string $name): string
    {
        return str_replace('_', '', ucwords($name, '_'));
    }

    /**
     * Convertit un nom en format snake_case
     */
    protected function toSnakeCase(string $name): string
    {
        return strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($name)));
    }

    /**
     * Crée une barre de progression
     */
    protected function createProgressBar(int $max = 0): ProgressBar
    {
        $progressBar = $this->io->createProgressBar($max);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        return $progressBar;
    }

    /**
     * Obtient le chemin absolu d'un répertoire
     */
    protected function getPath(string $relativePath): string
    {
        return rtrim(getcwd(), '/') . '/' . ltrim($relativePath, '/');
    }

    /**
     * Vérifie si un fichier est accessible en écriture
     */
    protected function isWritable(string $path): bool
    {
        $dir = is_file($path) ? dirname($path) : $path;
        return is_writable($dir);
    }

    /**
     * Formate une taille en bytes
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}