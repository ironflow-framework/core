<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFactoryCommand extends Command
{
   protected static $defaultName = 'make:factory';
   protected static $defaultDescription = 'Crée une nouvelle factory';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom de la factory')
         ->addArgument('model', InputArgument::REQUIRED, 'Le modèle associé')
         ->addArgument('fields', InputArgument::OPTIONAL, 'Les champs (format: nom:type,options)')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Écrase la factory si elle existe déjà');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $model = $input->getArgument('model');
      $fields = $input->getArgument('fields') ? explode(',', $input->getArgument('fields')) : [];

      $factoryPath = database_path("factories/{$name}Factory.php");

      if (Filesystem::exists($factoryPath) && !$input->getOption('force')) {
         $io->error("La factory {$name}Factory existe déjà !");
         return Command::FAILURE;
      }

      $factoryContent = $this->generateFactoryContent($name, $model, $fields);

      if (!Filesystem::exists(dirname($factoryPath))) {
         Filesystem::makeDirectory(dirname($factoryPath), 0755, true);
      }

      Filesystem::put($factoryPath, $factoryContent);
      $io->success("La factory {$name}Factory a été créée avec succès !");

      return Command::SUCCESS;
   }

   /**
    * Génère le contenu de la factory.
    */
   protected function generateFactoryContent(string $name, string $model, array $fields): string
   {
      $modelClass = "App\\Models\\{$model}";
      $fieldsContent = $this->generateFieldsContent($fields);

      return <<<PHP
<?php

namespace Database\Factories;

use IronFlow\Database\Factory;
use {$modelClass};

class {$name}Factory extends Factory
{
    /**
     * Le modèle associé à la factory.
     *
     * @var string
     */
    protected string \$model = {$model}::class;

    protected function configure(): void
    {
        \$this->states = [];
    }

    public function definition(): array
    {
        return [
{$fieldsContent}
        ];
    }
}
PHP;
   }

   /**
    * Génère le contenu des champs de la méthode definition().
    */
   protected function generateFieldsContent(array $fields): string
   {
      if (empty($fields)) {
         return "            // Ajoutez vos champs ici";
      }

      $lines = array_map(function ($field) {
         $parts = explode(':', $field);
         $name = $parts[0] ?? null;

         if (!$name) {
            return null;
         }

         $type = $parts[1] ?? 'word';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $fakerCall = "\$this->faker->{$type}";

         if (!empty($options)) {
            $args = implode(', ', array_map(function ($opt) {
               return is_numeric($opt) ? $opt : "'{$opt}'";
            }, $options));
            $fakerCall .= "({$args})";
         } else {
            $fakerCall .= "()";
         }

         return "            '{$name}' => {$fakerCall},";
      }, $fields);

      return implode("\n", array_filter($lines));
   }
}
