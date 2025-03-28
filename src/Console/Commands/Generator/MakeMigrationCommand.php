<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMigrationCommand extends Command
{
   protected static $defaultName = 'make:migration';
   protected static $defaultDescription = 'Crée une nouvelle migration';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom de la migration')
         ->addArgument('table', InputArgument::REQUIRED, 'Le nom de la table')
         ->addArgument('columns', InputArgument::OPTIONAL, 'Les colonnes (format: nom:type,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $table = $input->getArgument('table');
      $columns = $input->getArgument('columns') ? explode(',', $input->getArgument('columns')) : [];

      $timestamp = date('Y_m_d_His');
      $migrationContent = $this->generateMigrationContent($$table, $columns);
      $migrationPath = database_path("migrations/{$timestamp}_{$name}.php");

      if (!Filesystem::exists(dirname($migrationPath))) {
         Filesystem::makeDirectory(dirname($migrationPath), 0755, true);
      }

      Filesystem::put($migrationPath, $migrationContent);
      $io->success("La migration {$name} a été créée avec succès !");

      return Command::SUCCESS;
   }

   protected function generateMigrationContent(string $table, array $columns): string
   {
      $upContent = $this->generateUpContent($table, $columns);
      $downContent = $this->generateDownContent($table);

      return <<<PHP
<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use Ironflow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;


return new class extends Migration
{
    public function up(): void
    {
        {$upContent}
    }

    public function down(): void
    {
        {$downContent}
    }
};
PHP;
   }

   protected function generateUpContent(string $table, array $columns): string
   {
      $content = "Schema::createTable('{$table}', function (Anvil \$table) {\n";
      $content .= "            \$table->id();\n";

      foreach ($columns as $column) {
         $parts = explode(':', $column);
         $name = $parts[0];
         $type = $parts[1] ?? 'string';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $content .= "            \$table->{$type}('{$name}'";

         if (!empty($options)) {
            $content .= ", " . implode(', ', array_map(function ($option) {
               return is_numeric($option) ? $option : "'{$option}'";
            }, $options));
         }

         $content .= ");\n";
      }

      $content .= "            \$table->timestamps();\n";
      $content .= "        });";

      return $content;
   }

   protected function generateDownContent(string $table): string
   {
      return "Schema::dropTableIfExists('{$table}');";
   }
}
