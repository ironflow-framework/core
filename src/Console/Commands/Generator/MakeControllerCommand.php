<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeControllerCommand extends Command
{
   protected static $defaultName = 'make:controller';
   protected static $defaultDescription = 'Crée un nouveau contrôleur';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du contrôleur')
         ->addOption('resource', 'r', null, 'Créer un contrôleur avec les méthodes CRUD')
         ->addOption('api', 'a', null, 'Créer un contrôleur API');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $isResource = $input->getOption('resource');
      $isApi = $input->getOption('api');

      $controllerName = $this->formatControllerName($name);
      $namespace = $isApi ? 'App\\Controllers\\Api' : 'App\\Controllers';
      $className = $namespace . '\\' . $controllerName;

      $content = $this->generateControllerContent($className, $isResource, $isApi);
      $path = $this->getControllerPath($controllerName, $isApi);

      if (!is_dir(dirname($path))) {
         mkdir(dirname($path), 0755, true);
      }

      if (file_exists($path)) {
         $io->error("Le contrôleur {$controllerName} existe déjà !");
         return Command::FAILURE;
      }

      file_put_contents($path, $content);
      $io->success("Le contrôleur {$controllerName} a été créé avec succès !");

      return Command::SUCCESS;
   }

   private function formatControllerName(string $name): string
   {
      $name = str_replace(['Controller', '.php'], '', $name);
      return ucfirst($name) . 'Controller';
   }

   private function getControllerPath(string $name, bool $isApi): string
   {
      $basePath = app_path('Controllers');
      if ($isApi) {
         $basePath .= '/Api';
      }
      return $basePath . '/' . $name . '.php';
   }

   private function generateControllerContent(string $className, bool $isResource, bool $isApi): string
   {
      $baseClass = $isApi ? 'ApiController' : 'Controller';
      $content = "<?php\n\n";
      $content .= "namespace " . str_replace('\\' . basename($className), '', $className) . ";\n\n";
      $content .= "use IronFlow\\Http\\{$baseClass};\n";
      $content .= "use IronFlow\\Http\\Request;\n";
      $content .= "use IronFlow\\Http\\Response;\n\n";
      $content .= "class " . basename($className) . " extends {$baseClass}\n";
      $content .= "{\n";

      if ($isResource) {
         $content .= $this->generateResourceMethods();
      } else {
         $content .= "    public function index(Request \$request): Response\n";
         $content .= "    {\n";
         $content .= "        return \$this->view('index');\n";
         $content .= "    }\n";
      }

      $content .= "}\n";

      return $content;
   }

   private function generateResourceMethods(): string
   {
      $methods = [
         'index' => 'Afficher la liste des ressources',
         'create' => 'Afficher le formulaire de création',
         'store' => 'Enregistrer une nouvelle ressource',
         'show' => 'Afficher une ressource spécifique',
         'edit' => 'Afficher le formulaire de modification',
         'update' => 'Mettre à jour une ressource',
         'destroy' => 'Supprimer une ressource'
      ];

      $content = '';
      foreach ($methods as $method => $description) {
         $content .= "    /**\n";
         $content .= "     * {$description}\n";
         $content .= "     */\n";
         $content .= "    public function {$method}(Request \$request";
         if (in_array($method, ['show', 'edit', 'update', 'destroy'])) {
            $content .= ", \$id";
         }
         $content .= "): Response\n";
         $content .= "    {\n";
         $content .= "        // TODO: Implémenter la logique\n";
         $content .= "        return \$this->view('{$method}');\n";
         $content .= "    }\n\n";
      }

      return $content;
   }
}
