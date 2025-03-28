<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeComponentCommand extends Command
{
   protected static $defaultName = 'make:component';
   protected static $defaultDescription = 'Crée un nouveau composant';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du composant')
         ->addArgument('type', InputArgument::OPTIONAL, 'Le type de composant (UI|Layout|Form)', 'UI')
         ->addArgument('props', InputArgument::OPTIONAL, 'Les propriétés (format: nom:type,options)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $type = strtoupper($input->getArgument('type'));
      $props = $input->getArgument('props') ? explode(',', $input->getArgument('props')) : [];

      $componentContent = $this->generateComponentContent($name, $type, $props);
      $componentPath = "src/View/Components/{$type}/{$name}.php";

      if (!is_dir(dirname($componentPath))) {
         mkdir(dirname($componentPath), 0755, true);
      }

      file_put_contents($componentPath, $componentContent);
      $io->success("Le composant {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateComponentContent(string $name, string $type, array $props): string
   {
      $propsContent = $this->generatePropsContent($props);
      $baseClass = $this->getBaseClass($type);

      return <<<PHP
<?php

namespace IronFlow\View\Components\\{$type};

use IronFlow\View\Components\\{$baseClass};

class {$name} extends {$baseClass}
{
    public function __construct(
        {$propsContent}
    ) {
        parent::__construct();
    }

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
         $default = isset($parts[2]) ? " = {$parts[2]}" : '';

         $content .= "        public {$type} \${$name}{$default},\n";
      }

      return rtrim($content, ",\n");
   }

   protected function getBaseClass(string $type): string
   {
      return match ($type) {
         'UI' => 'UIComponent',
         'LAYOUT' => 'LayoutComponent',
         'FORM' => 'FormComponent',
         default => 'Component'
      };
   }
}
