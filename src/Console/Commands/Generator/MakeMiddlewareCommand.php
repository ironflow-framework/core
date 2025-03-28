<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMiddlewareCommand extends Command
{
   protected static $defaultName = 'make:middleware';
   protected static $defaultDescription = 'Crée un nouveau middleware';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du middleware');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');

      $middlewareContent = $this->generateMiddlewareContent($name);
      $middlewarePath = app_path("Middleware/{$name}.php");

      if (!Filesystem::exists(dirname($middlewarePath))) {
         Filesystem::makeDirectory(dirname($middlewarePath), 0755, true);
      }

      Filesystem::put($middlewarePath, $middlewareContent);
      $io->success("Le middleware {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateMiddlewareContent(string $name): string
   {
      return <<<PHP
<?php

namespace App\Middleware;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Http\Middleware;
class {$name} extends Middleware
{
    public function handle(Request \$request, callable \$next): Response
    {
        // Ajoutez votre logique de middleware ici
        
        return \$next(\$request);
    }
}
PHP;
   }
}
