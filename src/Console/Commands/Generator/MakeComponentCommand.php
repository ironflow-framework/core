<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Facades\Filesystem;
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

      $templateDefaultContent = <<<TWIG
      {% comment %} Enter your component template code here {% endcomment %}

      TWIG;
      $componentTemplatePath = view_path("templates/{$name}.twig");


      if (!Filesystem::isDirectory(dirname($componentPath))) {
         Filesystem::makeDirectory(dirname($componentPath));
      }

      if (!Filesystem::isDirectory(dirname($componentTemplatePath))) {
         Filesystem::makeDirectory(dirname($componentTemplatePath));
      }

      Filesystem::put($componentPath, $componentContent);
      Filesystem::put($componentTemplatePath, $templateDefaultContent);
      $io->success("Le composant {$name}Component a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateComponentContent(string $name, array $props): string
   {
      $propsArray = $this->generatePropsArray($props);

      return <<<PHP
<?php

namespace App\Components;

use IronFlow\Components\BaseComponent;

class {$name}Component extends BaseComponent
{
    public function render(): string
    {
        return \$this->view(strtolower('{$name}'), {$propsArray});
    }
}
PHP;
   }

   protected function generatePropsArray(array $props): string
   {
      if (empty($props)) {
         return '[]';
      }

      $index = 0;
      $array = "[\n";
      foreach ($props as $prop) {
         $parts = explode(':', $prop);
         $key = $parts[0];
         $array .= "            '{$key}' => \$this->props[{$index}] ?? null,\n";
         $index++;
      }
      $array .= "        ]";

      return $array;
   }
   
}
