<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeValidatorCommand extends Command
{
   protected static $defaultName = 'make:validator';
   protected static $defaultDescription = 'Crée un nouveau validateur';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du validateur')
         ->addArgument('rules', InputArgument::OPTIONAL, 'Les règles de validation (format: champ:règle,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $rules = $input->getArgument('rules') ? explode(',', $input->getArgument('rules')) : [];

      $validatorContent = $this->generateValidatorContent($name, $rules);
      $validatorPath = "src/Validation/{$name}.php";

      if (!is_dir(dirname($validatorPath))) {
         mkdir(dirname($validatorPath), 0755, true);
      }

      file_put_contents($validatorPath, $validatorContent);
      $io->success("Le validateur {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateValidatorContent(string $name, array $rules): string
   {
      $rulesContent = $this->generateRulesContent($rules);

      return <<<PHP
<?php

namespace App\Validation;

use IronFlow\Validation\Validator;

class {$name} extends Validator
{
    protected array \$rules = [
        {$rulesContent}
    ];

    protected array \$messages = [
        // Personnalisez vos messages d'erreur ici
    ];

    public function validate(array \$data): bool
    {
        return \$this->validateData(\$data);
    }
}
PHP;
   }

   protected function generateRulesContent(array $rules): string
   {
      if (empty($rules)) {
         return '';
      }

      $content = '';
      foreach ($rules as $rule) {
         $parts = explode(':', $rule);
         $field = $parts[0];
         $ruleString = $parts[1] ?? 'required';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $content .= "        '{$field}' => '{$ruleString}";

         if (!empty($options)) {
            $content .= ':' . implode('|', $options);
         }

         $content .= "',\n";
      }

      return rtrim($content, ",\n");
   }
}
