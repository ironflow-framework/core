<?php

declare(strict_types=1);

namespace IronFlow\Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Commande: make:module
 */
class MakeModuleCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('make:module')
             ->setDescription('Create a new module with interactive setup')
             ->addArgument('name', InputArgument::OPTIONAL, 'Module name')
             ->addOption('controller', 'c', InputOption::VALUE_NONE, 'Generate a controller')
             ->addOption('model', 'm', InputOption::VALUE_NONE, 'Generate a model')
             ->addOption('service', 's', InputOption::VALUE_NONE, 'Generate a service')
             ->addOption('migration', null, InputOption::VALUE_NONE, 'Generate a migration')
             ->addOption('seeder', null, InputOption::VALUE_NONE, 'Generate a seeder')
             ->addOption('all', 'a', InputOption::VALUE_NONE, 'Generate all components');
    }

    protected function handle(InputInterface $input, OutputInterface $output): int
    {
        // Obtenir le nom du module
        $name = $input->getArgument('name');
        if (!$name && !$input->getOption('no-interaction')) {
            $name = $this->askModuleName($input, $output);
        } elseif (!$name) {
            $output->writeln('<error>Module name is required when using --no-interaction</error>');
            return Command::FAILURE;
        }

        // Valider le nom
        if (!$this->isValidModuleName($name)) {
            $output->writeln('<error>Module name must start with uppercase and contain only letters and numbers</error>');
            return Command::FAILURE;
        }

        // Vérifier si le module existe déjà
        $modulePath = "modules/{$name}";
        if (is_dir($modulePath)) {
            $output->writeln("<error>Module {$name} already exists!</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Creating module: {$name}</info>");

        // Déterminer les composants à générer
        $components = $this->determineComponents($input, $output);
        
        // Déterminer le type de routes
        $routeType = $this->determineRouteType($input, $output, $components['controller']);

        // Créer le module
        $this->createModule($name, $modulePath, $components, $routeType);

        // Afficher le résumé
        $this->displaySummary($output, $name, $components);

        return Command::SUCCESS;
    }

    private function askModuleName(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        
        $question = new Question('Enter module name: ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Module name cannot be empty');
            }
            if (!$this->isValidModuleName($answer)) {
                throw new \RuntimeException('Module name must start with uppercase and contain only letters and numbers');
            }
            return $answer;
        });
        
        return $questionHelper->ask($input, $output, $question);
    }

    private function isValidModuleName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) === 1;
    }

    private function determineComponents(InputInterface $input, OutputInterface $output): array
    {
        $generateAll = $input->getOption('all');
        $noInteraction = $input->getOption('no-interaction');

        $components = [
            'controller' => $input->getOption('controller') || $generateAll,
            'model' => $input->getOption('model') || $generateAll,
            'service' => $input->getOption('service') || $generateAll,
            'migration' => $input->getOption('migration') || $generateAll,
            'seeder' => $input->getOption('seeder') || $generateAll,
        ];

        // Si aucune option n'est spécifiée et mode interactif
        if (!$generateAll && !array_filter($components) && !$noInteraction) {
            $components = $this->askForComponents($input, $output);
        }

        return $components;
    }

    private function askForComponents(InputInterface $input, OutputInterface $output): array
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        
        $output->writeln('<comment>What components would you like to generate?</comment>');
        
        return [
            'controller' => $questionHelper->ask($input, $output, 
                new ConfirmationQuestion('Generate Controller? [y/N] ', false)),
            'model' => $questionHelper->ask($input, $output, 
                new ConfirmationQuestion('Generate Model? [y/N] ', false)),
            'service' => $questionHelper->ask($input, $output, 
                new ConfirmationQuestion('Generate Service? [y/N] ', false)),
            'migration' => $questionHelper->ask($input, $output, 
                new ConfirmationQuestion('Generate Migration? [y/N] ', false)),
            'seeder' => $questionHelper->ask($input, $output, 
                new ConfirmationQuestion('Generate Seeder? [y/N] ', false)),
        ];
    }

    private function determineRouteType(InputInterface $input, OutputInterface $output, bool $hasController): string
    {
        if (!$hasController || $input->getOption('no-interaction')) {
            return 'resource';
        }

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        
        $routeQuestion = new ChoiceQuestion(
            'What type of routes would you like? (default: resource)',
            ['resource', 'api', 'custom', 'none'],
            0
        );
        
        return $questionHelper->ask($input, $output, $routeQuestion);
    }

    private function createModule(string $name, string $modulePath, array $components, string $routeType): void
    {
        // Créer la structure du module
        $this->createModuleStructure($name, $modulePath);

        // Générer les composants de base
        $this->generateModuleProvider($name, $modulePath);
        $this->generateRoutes($name, $modulePath, $routeType, $components['controller']);

        // Générer les composants optionnels
        if ($components['controller']) {
            $this->generateController($name, $modulePath);
        }

        if ($components['model']) {
            $this->generateModel($name, $modulePath);
        }

        if ($components['service']) {
            $this->generateService($name, $modulePath);
        }

        if ($components['migration']) {
            $this->generateMigration($name, $modulePath);
        }

        if ($components['seeder']) {
            $this->generateSeeder($name, $modulePath);
        }
    }

    private function createModuleStructure(string $name, string $modulePath): void
    {
        $this->ensureDirectoryExists($modulePath);
        $this->ensureDirectoryExists("{$modulePath}/Controllers");
        $this->ensureDirectoryExists("{$modulePath}/Services");
        $this->ensureDirectoryExists("{$modulePath}/Models");
        $this->ensureDirectoryExists("{$modulePath}/database/migrations");
        $this->ensureDirectoryExists("{$modulePath}/database/seeders");
        $this->ensureDirectoryExists("{$modulePath}/database/factories");
        $this->ensureDirectoryExists("{$modulePath}/resources/views");
        $this->ensureDirectoryExists("{$modulePath}/routes");
    }

    private function generateModuleProvider(string $name, string $modulePath): void
    {
        $providerContent = $this->generateFromTemplate('ModuleProvider', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/{$name}ModuleProvider.php", $providerContent);
    }

    private function generateRoutes(string $name, string $modulePath, string $routeType, bool $hasController): void
    {
        $templateName = match($routeType) {
            'api' => 'routes_api',
            'custom' => 'routes_custom',
            'none' => 'routes_empty',
            default => 'routes_resource'
        };

        $routesContent = $this->generateFromTemplate($templateName, [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'HAS_CONTROLLER' => $hasController
        ]);

        $this->writeFile("{$modulePath}/routes/routes.php", $routesContent);
    }

    private function generateController(string $name, string $modulePath): void
    {
        $controllerContent = $this->generateFromTemplate('Controller', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/Controllers/{$name}Controller.php", $controllerContent);
    }

    private function generateModel(string $name, string $modulePath): void
    {
        $modelContent = $this->generateFromTemplate('Model', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/Models/{$name}.php", $modelContent);
    }

    private function generateService(string $name, string $modulePath): void
    {
        $serviceContent = $this->generateFromTemplate('Service', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/Services/{$name}Service.php", $serviceContent);
    }

    private function generateMigration(string $name, string $modulePath): void
    {
        $timestamp = date('Y_m_d_His');
        $tableName = strtolower($name) . 's';
        
        $migrationContent = $this->generateFromTemplate('Migration', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'TABLE_NAME' => $tableName,
            'TIMESTAMP' => $timestamp
        ]);

        $this->writeFile("{$modulePath}/database/migrations/{$timestamp}_create_{$tableName}_table.php", $migrationContent);
    }

    private function generateSeeder(string $name, string $modulePath): void
    {
        $seederContent = $this->generateFromTemplate('Seeder', [
            'MODULE_NAME' => $name,
            'MODULE_NAME_LOWER' => strtolower($name),
            'MODULE_NAMESPACE' => "App\\Modules\\{$name}"
        ]);

        $this->writeFile("{$modulePath}/database/seeders/{$name}Seeder.php", $seederContent);
    }

    private function displaySummary(OutputInterface $output, string $name, array $components): void
    {
        $output->writeln('');
        $output->writeln("<info>✓ Module {$name} created successfully!</info>");
        $output->writeln('');
        $output->writeln('<comment>Generated components:</comment>');
        $output->writeln("  ✓ Module Provider");
        $output->writeln("  ✓ Routes");
        
        if ($components['controller']) $output->writeln("  ✓ Controller");
        if ($components['model']) $output->writeln("  ✓ Model");
        if ($components['service']) $output->writeln("  ✓ Service");
        if ($components['migration']) $output->writeln("  ✓ Migration");
        if ($components['seeder']) $output->writeln("  ✓ Seeder");
        
        $output->writeln('');
        $output->writeln('<comment>Next steps:</comment>');
        $output->writeln("  1. Register the module in bootstrap/app.php:");
        $output->writeln("     \$app->registerModule(App\\Modules\\{$name}\\{$name}ModuleProvider::class);");
        
        if ($components['migration']) {
            $output->writeln("  2. Run the migration:");
            $output->writeln("     php forge migrate:run");
        }
        
        if ($components['seeder']) {
            $output->writeln("  3. Run the seeder (optional):");
            $output->writeln("     php forge db:seed {$name}Seeder");
        }
        
        $output->writeln('');
    }
}