<?php

declare(strict_types=1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Commande serve - Serveur de développement avancé
 * 
 * Lance un serveur de développement avec des fonctionnalités avancées :
 * - Détection automatique de port libre
 * - Surveillance des fichiers (optionnel)
 * - Configuration personnalisable
 * - Gestion des erreurs améliorée
 */
class ServeCommand extends BaseCommand
{
    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_PORT = 8000;
    private const MAX_PORT_ATTEMPTS = 10;

    protected function configure(): void
    {
        $this->setName('serve')
             ->setDescription('Start the IronFlow development server')
             ->setHelp('This command starts a local development server for your IronFlow application.')
             ->addOption(
                 'host',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'The host address to bind to',
                 self::DEFAULT_HOST
             )
             ->addOption(
                 'port',
                 'p',
                 InputOption::VALUE_OPTIONAL,
                 'The port number to use',
                 self::DEFAULT_PORT
             )
             ->addOption(
                 'auto-port',
                 null,
                 InputOption::VALUE_NONE,
                 'Automatically find an available port if the specified port is busy'
             )
             ->addOption(
                 'open',
                 'o',
                 InputOption::VALUE_NONE,
                 'Open the application in your default browser'
             )
             ->addOption(
                 'env',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Environment to run the server in',
                 'development'
             )
             ->addOption(
                 'router',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Path to a custom router script'
             );
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = (int) $input->getOption('port');
        $autoPort = $input->getOption('auto-port');
        $shouldOpen = $input->getOption('open');
        $environment = $input->getOption('env');
        $routerScript = $input->getOption('router');

        // Validation de l'environnement
        $this->validateEnvironment();

        // Trouver un port disponible si nécessaire
        $finalPort = $autoPort ? $this->findAvailablePort($host, $port) : $port;
        
        if ($finalPort !== $port) {
            $this->io->note("Port {$port} is busy, using port {$finalPort} instead");
        }

        // Vérifier si le port est disponible
        if (!$autoPort && !$this->isPortAvailable($host, $finalPort)) {
            $this->io->error("Port {$finalPort} is already in use. Use --auto-port to find an available port automatically.");
            return Command::FAILURE;
        }

        $documentRoot = $this->getDocumentRoot();
        $serverUrl = "http://{$host}:{$finalPort}";

        // Affichage des informations de démarrage
        $this->displayServerInfo($serverUrl, $documentRoot, $environment);

        // Configurer les variables d'environnement
        $this->setupEnvironment($environment);

        // Démarrer le serveur
        $process = $this->createServerProcess($host, $finalPort, $documentRoot, $routerScript);
        
        // Ouvrir le navigateur si demandé
        if ($shouldOpen) {
            $this->openBrowser($serverUrl);
        }

        // Gérer les signaux pour un arrêt propre
        $this->setupSignalHandlers($process);

        try {
            $this->logger->info('Development server starting', [
                'host' => $host,
                'port' => $finalPort,
                'document_root' => $documentRoot,
                'environment' => $environment
            ]);

            $process->run(function ($type, $buffer) {
                // Afficher la sortie du serveur en temps réel
                if ($type === Process::ERR) {
                    $this->io->write("<error>{$buffer}</error>");
                } else {
                    $this->io->write($buffer);
                }
            });

            return $process->getExitCode() ?? Command::SUCCESS;

        } catch (Throwable $e) {
            $this->logger->error('Server process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->io->error("Server failed to start: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Valide l'environnement de développement
     */
    private function validateEnvironment(): void
    {
        $documentRoot = $this->getDocumentRoot();
        
        if (!is_dir($documentRoot)) {
            throw new \RuntimeException(
                "Document root directory '{$documentRoot}' does not exist. " .
                "Make sure you're running this command from your project root."
            );
        }

        $indexFile = $documentRoot . '/index.php';
        if (!file_exists($indexFile)) {
            $this->io->warning(
                "No index.php found in '{$documentRoot}'. " .
                "Make sure your application is properly set up."
            );
        }

        // Vérifier la version PHP
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->io->warning('IronFlow recommends PHP 8.0 or higher for optimal performance.');
        }
    }

    /**
     * Trouve un port disponible
     */
    private function findAvailablePort(string $host, int $startPort): int
    {
        for ($i = 0; $i < self::MAX_PORT_ATTEMPTS; $i++) {
            $testPort = $startPort + $i;
            
            if ($this->isPortAvailable($host, $testPort)) {
                return $testPort;
            }
        }

        throw new \RuntimeException(
            "Could not find an available port after testing {$startPort}-" . 
            ($startPort + self::MAX_PORT_ATTEMPTS - 1)
        );
    }

    /**
     * Vérifie si un port est disponible
     */
    private function isPortAvailable(string $host, int $port): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 1);
        
        if ($socket) {
            fclose($socket);
            return false; // Port occupé
        }
        
        return true; // Port disponible
    }

    /**
     * Obtient le répertoire racine des documents
     */
    private function getDocumentRoot(): string
    {
        $candidates = ['public', 'web', 'www'];
        
        foreach ($candidates as $candidate) {
            $path = $this->getPath($candidate);
            if (is_dir($path)) {
                return $path;
            }
        }
        
        // Fallback vers le répertoire courant
        return getcwd();
    }

    /**
     * Affiche les informations du serveur
     */
    private function displayServerInfo(string $url, string $documentRoot, string $environment): void
    {
        $this->io->title('IronFlow Development Server');
        
        $this->io->definitionList(
            ['Server URL' => "<href={$url}>{$url}</>"],
            ['Document Root' => $documentRoot],
            ['Environment' => $environment],
            ['PHP Version' => PHP_VERSION],
            ['Memory Limit' => ini_get('memory_limit')],
            ['Max Execution Time' => ini_get('max_execution_time') . 's']
        );

        $this->io->note('Press Ctrl+C to stop the server');
        $this->io->newLine();
    }

    /**
     * Configure les variables d'environnement
     */
    private function setupEnvironment(string $environment): void
    {
        putenv("APP_ENV={$environment}");
        putenv("APP_DEBUG=" . ($environment === 'development' ? 'true' : 'false'));
        
        // Charger le fichier .env si disponible
        $envFile = $this->getPath('.env');
        if (file_exists($envFile)) {
            $this->loadEnvFile($envFile);
        }
    }

    /**
     * Charge un fichier .env
     */
    private function loadEnvFile(string $envFile): void
    {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue; // Ignorer les commentaires et lignes invalides
            }
            
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            if (!empty($key)) {
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Crée le processus du serveur
     */
    private function createServerProcess(string $host, int $port, string $documentRoot, ?string $routerScript): Process
    {
        $command = [
            PHP_BINARY,
            '-S',
            "{$host}:{$port}",
            '-t',
            $documentRoot
        ];

        // Ajouter le script de routage personnalisé si spécifié
        if ($routerScript) {
            if (!file_exists($routerScript)) {
                throw new \InvalidArgumentException("Router script '{$routerScript}' not found");
            }
            $command[] = $routerScript;
        } else {
            // Utiliser le routeur par défaut s'il existe
            $defaultRouter = $documentRoot . '/router.php';
            if (file_exists($defaultRouter)) {
                $command[] = $defaultRouter;
            }
        }

        $process = new Process($command);
        $process->setTimeout(null); // Pas de timeout pour le serveur
        
        return $process;
    }

    /**
     * Ouvre l'URL dans le navigateur par défaut
     */
    private function openBrowser(string $url): void
    {
        $this->io->note('Opening browser...');
        
        try {
            $os = PHP_OS_FAMILY;
            
            switch ($os) {
                case 'Windows':
                    $command = ['cmd', '/c', 'start', $url];
                    break;
                case 'Darwin': // macOS
                    $command = ['open', $url];
                    break;
                case 'Linux':
                default:
                    $command = ['xdg-open', $url];
                    break;
            }
            
            $process = new Process($command);
            $process->run();
            
            if (!$process->isSuccessful()) {
                $this->io->note("Could not open browser automatically. Please visit: {$url}");
            }
            
        } catch (Throwable $e) {
            $this->logger->warning('Failed to open browser', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            $this->io->note("Could not open browser automatically. Please visit: {$url}");
        }
    }

    /**
     * Configure les gestionnaires de signaux pour un arrêt propre
     */
    private function setupSignalHandlers(Process $process): void
    {
        if (!extension_loaded('pcntl')) {
            return; // Extension PCNTL non disponible
        }

        // Gestionnaire pour SIGINT (Ctrl+C) et SIGTERM
        $signalHandler = function (int $signal) use ($process) {
            $this->io->newLine();
            $this->io->note('Shutting down server...');
            
            $this->logger->info('Server shutdown requested', ['signal' => $signal]);
            
            if ($process->isRunning()) {
                $process->stop();
            }
            
            exit(0);
        };

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, $signalHandler);
            pcntl_signal(SIGTERM, $signalHandler);
        }
    }

    /**
     * Obtient les informations système pour le debug
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'os' => PHP_OS,
            'architecture' => php_uname('m'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'extensions' => get_loaded_extensions()
        ];
    }

    /**
     * Vérifie les prérequis du serveur
     */
    private function checkServerRequirements(): array
    {
        $requirements = [];
        
        // Vérifier les extensions PHP importantes
        $requiredExtensions = ['json', 'mbstring', 'openssl', 'pdo', 'tokenizer'];
        $recommendedExtensions = ['curl', 'gd', 'intl', 'zip', 'bcmath'];
        
        foreach ($requiredExtensions as $ext) {
            $requirements['extensions']['required'][$ext] = extension_loaded($ext);
        }
        
        foreach ($recommendedExtensions as $ext) {
            $requirements['extensions']['recommended'][$ext] = extension_loaded($ext);
        }
        
        // Vérifier les permissions
        $requirements['permissions']['document_root_writable'] = is_writable($this->getDocumentRoot());
        $requirements['permissions']['storage_writable'] = is_writable($this->getPath('storage'));
        
        // Vérifier la configuration PHP
        $requirements['php']['version_ok'] = version_compare(PHP_VERSION, '8.0.0', '>=');
        $requirements['php']['memory_limit'] = $this->parseMemoryLimit(ini_get('memory_limit')) >= 128 * 1024 * 1024;
        
        return $requirements;
    }

    /**
     * Parse une limite de mémoire PHP
     */
    private function parseMemoryLimit(string $memLimit): int
    {
        if ($memLimit === '-1') {
            return PHP_INT_MAX;
        }
        
        $memLimit = trim($memLimit);
        $last = strtolower($memLimit[strlen($memLimit) - 1]);
        $memLimit = (int) $memLimit;
        
        switch ($last) {
            case 'g':
                $memLimit *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $memLimit *= 1024 * 1024;
                break;
            case 'k':
                $memLimit *= 1024;
                break;
        }
        
        return $memLimit;
    }

    /**
     * Affiche un diagnostic du serveur si en mode verbose
     */
    private function displayDiagnostics(OutputInterface $output): void
    {
        if (!$output->isVerbose()) {
            return;
        }
        
        $this->io->section('Server Diagnostics');
        
        $requirements = $this->checkServerRequirements();
        $systemInfo = $this->getSystemInfo();
        
        // Afficher les extensions manquantes
        $missingRequired = array_filter($requirements['extensions']['required'], fn($loaded) => !$loaded);
        if (!empty($missingRequired)) {
            $this->io->error('Missing required PHP extensions: ' . implode(', ', array_keys($missingRequired)));
        }
        
        $missingRecommended = array_filter($requirements['extensions']['recommended'], fn($loaded) => !$loaded);
        if (!empty($missingRecommended)) {
            $this->io->warning('Missing recommended PHP extensions: ' . implode(', ', array_keys($missingRecommended)));
        }
        
        // Afficher les problèmes de permissions
        if (!$requirements['permissions']['document_root_writable']) {
            $this->io->warning('Document root is not writable');
        }
        
        if (!$requirements['permissions']['storage_writable']) {
            $this->io->warning('Storage directory is not writable');
        }
        
        // Informations système
        $this->io->definitionList(
            ['System' => $systemInfo['os'] . ' (' . $systemInfo['architecture'] . ')'],
            ['PHP SAPI' => $systemInfo['php_sapi']],
            ['Loaded Extensions' => count($systemInfo['extensions']) . ' extensions loaded']
        );
    }
}