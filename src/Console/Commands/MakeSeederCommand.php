<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeSeederCommand extends Command
{
   protected static $defaultName = 'make:seeder';
   protected static $defaultDescription = 'Crée un nouveau seeder';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du seeder')
         ->addArgument('model', InputArgument::OPTIONAL, 'Le modèle associé');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $model = $input->getArgument('model');

      $seederContent = $this->generateSeederContent($name, $model);
      $seederPath = "database/seeders/{$name}.php";

      if (!is_dir(dirname($seederPath))) {
         mkdir(dirname($seederPath), 0755, true);
      }

      file_put_contents($seederPath, $seederContent);
      $io->success("Le seeder {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateSeederContent(string $name, ?string $model): string
   {
      $modelClass = $model ? "IronFlow\\Models\\{$model}" : null;
      $modelUse = $modelClass ? "use {$modelClass};\n" : '';

      return <<<PHP
<?php

namespace Database\Seeders;

use IronFlow\Database\Seeder;
{$modelUse}

class {$name} extends Seeder
{
    public function run(): void
    {
        // Exemple de données
        \$data = [
            // Ajoutez vos données ici
        ];

        // Si un modèle est spécifié, utilisez-le pour insérer les données
        if (class_exists('{$modelClass}')) {
            foreach (\$data as \$item) {
                {$model}::create(\$item);
            }
        }
    }
}
PHP;
   }
}
