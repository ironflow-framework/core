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
   protected function configure(): void
   {
      $this
         ->setName('make:controller')
         ->setDescription('Crée un nouveau contrôleur')
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du contrôleur')
         ->addOption('resource', 'r', null, 'Créer un contrôleur avec les méthodes CRUD')
         ->addOption('api', 'a', null, 'Créer un contrôleur API')
         ->addOption('force', 'f', null, 'Écraser le contrôleur s\'il existe déjà');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $name = $this->sanitizeName($input->getArgument('name'));
      $isResource = $input->getOption('resource');
      $isApi = $input->getOption('api');
      $force = $input->getOption('force');

      $controllerName = $this->formatControllerName($name);
      $namespace = $this->getNamespace($isApi);
      $className = "{$namespace}\\{$controllerName}";

      $content = $this->generateControllerContent($className, $isResource, $isApi);
      $path = $this->getControllerPath($controllerName, $isApi);

      if (!is_dir(dirname($path))) {
         mkdir(dirname($path), 0755, true);
      }

      if (file_exists($path) && !$force) {
         $io->error("Le contrôleur {$controllerName} existe déjà ! Utilisez --force pour écraser.");
         return Command::FAILURE;
      }

      file_put_contents($path, $content);
      $io->success("Le contrôleur {$controllerName} a été créé avec succès !");

      return Command::SUCCESS;
   }

   private function sanitizeName(string $name): string
   {
      return preg_replace('/[^A-Za-z0-9]/', '', $name);
   }

   private function formatControllerName(string $name): string
   {
      $name = str_replace(['Controller', '.php'], '', $name);
      return ucfirst($name) . 'Controller';
   }

   private function getNamespace(bool $isApi): string
   {
      return $isApi ? 'App\\Controllers\\Api' : 'App\\Controllers';
   }

   private function getControllerPath(string $name, bool $isApi): string
   {
      $basePath = app_path('Controllers');
      if ($isApi) {
         $basePath .= '/Api';
      }
      return "{$basePath}/{$name}.php";
   }

   private function generateControllerContent(string $className, bool $isResource, bool $isApi): string
   {
      $baseClass = $isApi ? 'ApiController' : 'Controller';

      $namespace = str_replace('\\' . basename($className), '', $className);
      $controllerName = basename($className);

      $content = "<?php\n\n";
      $content .= "namespace {$namespace};\n\n";
      $content .= "use IronFlow\\Http\\{$baseClass};\n";
      $content .= "use IronFlow\\Http\\Request;\n";
      $content .= "use IronFlow\\Http\\Response;\n\n";
      $content .= "class {$controllerName} extends {$baseClass}\n";
      $content .= "{\n";

      if ($isResource) {
         $content .= $this->generateResourceMethods();
      } else {
         $content .= "    /**\n";
         $content .= "     * Affiche la page d'accueil.\n";
         $content .= "     *\n";
         $content .= "     * @param Request \$request\n";
         $content .= "     * @return Response\n";
         $content .= "     */\n";
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
         'index'   => 'Afficher la liste des ressources',
         'create'  => 'Afficher le formulaire de création',
         'store'   => 'Enregistrer une nouvelle ressource',
         'show'    => 'Afficher une ressource spécifique',
         'edit'    => 'Afficher le formulaire de modification',
         'update'  => 'Mettre à jour une ressource',
         'destroy' => 'Supprimer une ressource',
      ];

      $content = '';
      foreach ($methods as $method => $description) {
         $content .= "    /**\n";
         $content .= "     * {$description}\n";
         $content .= "     *\n";
         $content .= "     * @param Request \$request\n";
         if (in_array($method, ['show', 'edit', 'update', 'destroy'])) {
            $content .= "     * @param mixed \$id\n";
         }
         $content .= "     * @return Response\n";
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
