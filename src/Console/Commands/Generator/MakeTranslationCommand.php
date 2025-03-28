<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Filesystem;

/**
 * Commande pour générer des fichiers de traduction
 */
class MakeTranslationCommand extends Command
{
   /**
    * Nom de la commande.
    *
    * @var string
    */
   protected static $defaultName = 'make:translation';

   /**
    * Description de la commande.
    *
    * @var string
    */
   protected static $defaultDescription = 'Crée un nouveau fichier de traduction';

   /**
    * Style Symfony
    */
   protected SymfonyStyle $io;

   /**
    * Configure la commande.
    *
    * @return void
    */
   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Nom du fichier de traduction')
         ->addOption('locale', 'l', InputOption::VALUE_OPTIONAL, 'Langue(s) à créer (séparées par des virgules)', 'fr,en')
         ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format du fichier (php, json, yaml)', 'php');
   }

   /**
    * Exécute la commande.
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return int
    */
   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $this->io = new SymfonyStyle($input, $output);

      $name = $input->getArgument('name');
      $locales = explode(',', $input->getOption('locale'));
      $format = $input->getOption('format');

      foreach ($locales as $locale) {
         $this->createTranslationFile($name, trim($locale), $format);
      }

      return Command::SUCCESS;
   }

   /**
    * Crée un fichier de traduction.
    *
    * @param string $name
    * @param string $locale
    * @param string $format
    * @return void
    */
   protected function createTranslationFile(string $name, string $locale, string $format): void
   {
      $langPath = lang_path($locale);

      if (!is_dir($langPath)) {
         mkdir($langPath, 0755, true);
         $this->io->success("Dossier de langue '{$locale}' créé.");
      }

      $extension = $this->getExtension($format);
      $filename = $langPath . '/' . $name . '.' . $extension;

      if (file_exists($filename)) {
         $this->io->warning("Le fichier de traduction '{$name}' existe déjà pour la langue '{$locale}'.");
         return;
      }

      $content = $this->getStubContent($format);
      $fs = new Filesystem();
      $fs->put($filename, $content);

      $this->io->success("Fichier de traduction '{$name}.{$extension}' créé pour la langue '{$locale}'.");
   }

   /**
    * Obtient l'extension du fichier en fonction du format.
    *
    * @param string $format
    * @return string
    */
   protected function getExtension(string $format): string
   {
      return match (strtolower($format)) {
         'json' => 'json',
         'yaml', 'yml' => 'yaml',
         default => 'php',
      };
   }

   /**
    * Obtient le contenu du stub en fonction du format.
    *
    * @param string $format
    * @return string
    */
   protected function getStubContent(string $format): string
   {
      return match (strtolower($format)) {
         'json' => <<<'JSON'
{
    "example": "Exemple de traduction",
    "welcome": "Bienvenue"
}
JSON,
         'yaml', 'yml' => <<<'YAML'
example: Exemple de traduction
welcome: Bienvenue
YAML,
         default => <<<'PHP'
<?php

return [
    'example' => 'Exemple de traduction',
    'welcome' => 'Bienvenue',
];
PHP,
      };
   }
}
