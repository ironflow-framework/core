<?php

namespace IronFlow\Console\Commands\Generator;

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
      $middlewarePath = "src/Http/Middleware/{$name}.php";

      if (!is_dir(dirname($middlewarePath))) {
         mkdir(dirname($middlewarePath), 0755, true);
      }

      file_put_contents($middlewarePath, $middlewareContent);
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

class {$name}
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
