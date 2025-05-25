<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Facades\Str;

class MakeComponentCommand extends Command
{
   protected static $defaultName = 'make:component';
   protected static $defaultDescription = 'Crée un nouveau composant';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du composant')
         ->addArgument('props', InputArgument::OPTIONAL, 'Les propriétés (format: nom:type,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = Str::studly($input->getArgument('name'));
      $props = $input->getArgument('props') ? explode(',', $input->getArgument('props')) : [];

      $componentContent = $this->generateComponentContent($name, $props);
      $componentPath = app_path("Components/{$name}Component.php");

      if (!is_dir(dirname($componentPath))) {
         mkdir(dirname($componentPath), 0755, true);
      }

      file_put_contents($componentPath, $componentContent);
      $io->success("Le composant {$name}Component a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateComponentContent(string $name, array $props): string
   {
      $propsContent = $this->generatePropsContent($props);

      return <<<PHP
<?php

namespace App\Components;

use IronFlow\Components\BaseComponent;

class {$name}Component extends BaseComponent
{
{$propsContent}

    public function render(): string
    {
        return <<<'HTML'
            <!-- Ajoutez votre template HTML ici -->
        HTML;
    }
}
PHP;
   }

   protected function generatePropsContent(array $props): string
   {
      if (empty($props)) {
         return '';
      }

      $content = '';
      foreach ($props as $prop) {
         $parts = explode(':', $prop);
         $name = $parts[0];
         $type = $parts[1] ?? 'mixed';
         $default = isset($parts[2]) ? " = {$parts[2]};" : ';';

         $content .= "        public {$type} \${$name}{$default},\n";
      }

      return rtrim($content, ",\n");
   }

  
}
