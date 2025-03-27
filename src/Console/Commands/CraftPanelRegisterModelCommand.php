<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands;

use IronFlow\Console\Command;
use ReflectionClass;

class CraftPanelRegisterModelCommand extends Command
{
    protected string $signature = 'craft:panel:register-model {model : The model class name}';
    protected string $description = 'Register a model in the CraftPanel administration interface';

    public function handle(): int
    {
        $modelClass = $this->argument('model');
        
        // Vérifier si la classe existe
        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found.");
            return Command::FAILURE;
        }

        // Vérifier si c'est bien un Model
        if (!is_subclass_of($modelClass, \IronFlow\Database\Model::class)) {
            $this->error("Class {$modelClass} must extend IronFlow\Database\Model.");
            return Command::FAILURE;
        }

        // Créer le fichier de configuration pour le modèle
        $this->createModelConfig($modelClass);

        $this->info("Model {$modelClass} has been registered in CraftPanel successfully!");
        return Command::SUCCESS;
    }

    protected function createModelConfig(string $modelClass): void
    {
        $reflection = new ReflectionClass($modelClass);
        $modelName = $reflection->getShortName();
        $configPath = "app/CraftPanel/Config/{$modelName}Config.php";

        $content = <<<PHP
<?php

namespace App\CraftPanel\Config;

use {$modelClass};
use IronFlow\CraftPanel\Traits\Administrable;

class {$modelName}Config
{
    public static function register(): void
    {
        {$modelClass}::configureAdmin([
            'displayName' => '{$modelName}',
            'pluralName' => strtolower('{$modelName}s'),
            'perPage' => 10,
            'searchable' => ['id', 'created_at'],
            'filterable' => ['id', 'created_at'],
        ]);

        {$modelClass}::setAdminFields([
            'id' => ['type' => 'number', 'label' => 'ID', 'sortable' => true],
            'created_at' => ['type' => 'datetime', 'label' => 'Created At', 'sortable' => true],
            'updated_at' => ['type' => 'datetime', 'label' => 'Updated At', 'sortable' => true],
        ]);

        {$modelClass}::setAdminValidation([
            // Définir ici les règles de validation
        ]);

        {$modelClass}::setAdminRelations([
            // Définir ici les relations à afficher
        ]);

        {$modelClass}::setAdminActions([
            'create' => true,
            'edit' => true,
            'delete' => true,
            'export' => true,
        ]);
    }
}
PHP;

        // Créer le dossier si nécessaire
        $dir = dirname($configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($configPath, $content);
        $this->info("Created model configuration: {$configPath}");

        // Ajouter l'enregistrement au service provider
        $this->updateServiceProvider($modelName);
    }

    protected function updateServiceProvider(string $modelName): void
    {
        $providerPath = "app/Providers/CraftPanelServiceProvider.php";
        if (!file_exists($providerPath)) {
            $content = <<<PHP
<?php

namespace App\Providers;

use IronFlow\Support\ServiceProvider;

class CraftPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \$this->registerAdminModels();
    }

    protected function registerAdminModels(): void
    {
        // Les modèles enregistrés seront ajoutés ici
    }
}
PHP;
            file_put_contents($providerPath, $content);
        }

        // Ajouter le modèle au provider
        $content = file_get_contents($providerPath);
        $registerMethod = "protected function registerAdminModels(): void\n    {";
        $newRegistration = "        \App\CraftPanel\Config\\{$modelName}Config::register();";
        
        if (!str_contains($content, $newRegistration)) {
            $content = str_replace(
                $registerMethod,
                $registerMethod . "\n" . $newRegistration,
                $content
            );
            file_put_contents($providerPath, $content);
        }
    }
}
