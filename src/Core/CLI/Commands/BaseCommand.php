<?php 

declare(strict_types= 1);

namespace IronFlow\Core\CLI\Commands;

use IronFlow\Core\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande de base
 */
abstract class BaseCommand extends Command
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    abstract protected function execute(InputInterface $input, OutputInterface $output): int;

    /**
     * Crée un répertoire s'il n'existe pas
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Génère le contenu d'un fichier à partir d'un template
     */
    protected function generateFromTemplate(string $template, array $replacements = []): string
    {
        $content = $this->getTemplate($template);
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{$search}}", $replace, $content);
        }

        return $content;
    }

    /**
     * Obtient le contenu d'un template
     */
    protected function getTemplate(string $name): string
    {
        $templatePath = __DIR__ . "/Templates/{$name}.php.template";
        
        if (!file_exists($templatePath)) {
            throw new \InvalidArgumentException("Template {$name} not found");
        }

        return file_get_contents($templatePath);
    }

    /**
     * Écrit un fichier avec confirmation
     */
    protected function writeFile(string $path, string $content, OutputInterface $output): bool
    {
        if (file_exists($path)) {
            $output->writeln("<error>File already exists: {$path}</error>");
            return false;
        }

        $this->ensureDirectoryExists(dirname($path));
        file_put_contents($path, $content);
        
        $output->writeln("<info>Created: {$path}</info>");
        return true;
    }
}