<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFormCommand extends Command
{
   protected static $defaultName = 'make:form';
   protected static $defaultDescription = 'Crée un nouveau formulaire';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du formulaire')
         ->addArgument('model', InputArgument::OPTIONAL, 'Le model associé au formulaire')
         ->addArgument('fields', InputArgument::OPTIONAL, 'Les champs (format: nom:type,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $model = $input->getArgument('model');
      $fields = $input->getArgument('fields') ? explode(',', $input->getArgument('fields')) : [];

      $formContent = $this->generateFormContent($name, $model, $fields);
      $formPath = app_path("Components/Forms/") . "{$name}.php";

      if (!is_dir(dirname($formPath))) {
         mkdir(dirname($formPath), 0755, true);
      }

      file_put_contents($formPath, $formContent);
      $io->success("Le formulaire {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateFormContent(string $name, string $model, array $fields): string
   {
      $fieldsContent = $this->generateFieldsContent($fields);

      $modelAttached = isset($model) ? "use App\Models\{$model};" : "";

      return <<<PHP
<?php

namespace App\Components\Forms;

use IronFlow\Forms\Form;
{$modelAttached};

class {$name} extends Form
{
    public function __construct()
    {
        parent::__construct();
        
        {$fieldsContent}
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
         $type = $parts[1] ?? 'text';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $optionsArray = [];
         foreach ($options as $option) {
            if (strpos($option, '=') !== false) {
               list($key, $value) = explode('=', $option);
               $optionsArray[$key] = $value;
            } else {
               $optionsArray[$option] = true;
            }
         }

         $optionsString = $this->formatOptions($optionsArray);

         $content .= "        \$this->addField('{$name}', '{$type}', {$optionsString});\n";
      }

      return rtrim($content, "\n");
   }

   protected function formatOptions(array $options): string
   {
      if (empty($options)) {
         return '[]';
      }

      $formatted = [];
      foreach ($options as $key => $value) {
         if (is_bool($value)) {
            $formatted[] = "'{$key}' => " . ($value ? 'true' : 'false');
         } else {
            $formatted[] = "'{$key}' => '{$value}'";
         }
      }

      return "[\n            " . implode(",\n            ", $formatted) . "\n        ]";
   }
}
