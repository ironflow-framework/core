<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeServiceCommand extends Command
{
   protected static $defaultName = 'make:service';
   protected static $defaultDescription = 'Crée un nouveau service';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du service')
         ->addArgument('dependencies', InputArgument::OPTIONAL, 'Les dépendances (format: type:nom)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $dependencies = $input->getArgument('dependencies') ? explode(',', $input->getArgument('dependencies')) : [];

      $serviceContent = $this->generateServiceContent($name, $dependencies);
      $servicePath = "src/Services/{$name}.php";

      if (!is_dir(dirname($servicePath))) {
         mkdir(dirname($servicePath), 0755, true);
      }

      file_put_contents($servicePath, $serviceContent);
      $io->success("Le service {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateServiceContent(string $name, array $dependencies): string
   {
      $depsContent = $this->generateDependenciesContent($dependencies);
      $constructorContent = $this->generateConstructorContent($dependencies);

      return <<<PHP
<?php

namespace App\Services;

class {$name}
{
    {$depsContent}

    public function __construct(
        {$constructorContent}
    ) {
    }

    // Ajoutez vos méthodes de service ici
}
PHP;
   }

   protected function generateDependenciesContent(array $dependencies): string
   {
      if (empty($dependencies)) {
         return '';
      }

      $content = '';
      foreach ($dependencies as $dep) {
         $parts = explode(':', $dep);
         $type = $parts[0];
         $name = $parts[1] ?? lcfirst($type);

         $content .= "    private {$type} \${$name};\n";
      }

      return rtrim($content, "\n");
   }

   protected function generateConstructorContent(array $dependencies): string
   {
      if (empty($dependencies)) {
         return '';
      }

      $content = '';
      foreach ($dependencies as $dep) {
         $parts = explode(':', $dep);
         $type = $parts[0];
         $name = $parts[1] ?? lcfirst($type);

         $content .= "        {$type} \${$name},\n";
      }

      return rtrim($content, ",\n");
   }
}
