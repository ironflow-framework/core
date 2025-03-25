<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
         ->addArgument('fields', InputArgument::OPTIONAL, 'Les champs (format: nom:type,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $model = $input->getArgument('model');
      $fields = $input->getArgument('fields') ? explode(',', $input->getArgument('fields')) : [];

      $factoryContent = $this->generateFactoryContent($name, $model, $fields);
      $factoryPath = "database/factories/{$name}.php";

      if (!is_dir(dirname($factoryPath))) {
         mkdir(dirname($factoryPath), 0755, true);
      }

      file_put_contents($factoryPath, $factoryContent);
      $io->success("La factory {$name} a été créée avec succès !");

      return Command::SUCCESS;
   }

   protected function generateFactoryContent(string $name, string $model, array $fields): string
   {
      $modelClass = "IronFlow\\Models\\{$model}";
      $fieldsContent = $this->generateFieldsContent($fields);

      return <<<PHP
<?php

namespace IronFlow\Database\Factories;

use IronFlow\Database\Factory;
use {$modelClass};

class {$name} extends Factory
{
    protected \$model = {$model}::class;

    public function definition(): array
    {
        return [
            {$fieldsContent}
        ];
    }
}
PHP;
   }

   protected function generateFieldsContent(array $fields): string
   {
      $content = '';
      foreach ($fields as $field) {
         $parts = explode(':', $field);
         $name = $parts[0];
         $type = $parts[1] ?? 'string';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $content .= "            '{$name}' => \$this->faker->{$type}";

         if (!empty($options)) {
            $content .= "(" . implode(', ', array_map(function ($option) {
               return is_numeric($option) ? $option : "'{$option}'";
            }, $options)) . ")";
         }

         $content .= ",\n";
      }

      return rtrim($content, ",\n");
   }
}
