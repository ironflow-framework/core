<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScaffoldCommand extends Command
{
    protected static $defaultName = 'scaffold';
    protected static $defaultDescription = 'Génère un scaffold complet pour un modèle';

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Le nom du modèle')
            ->addArgument('fields', InputArgument::OPTIONAL, 'Les champs (format: nom:type,options)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $fields = $input->getArgument('fields') ? explode(',', $input->getArgument('fields')) : [];

        // Créer le modèle
        $this->createModel($name, $fields);
        $io->success("Le modèle {$name} a été créé");

        // Créer la migration
        $this->createMigration($name, $fields);
        $io->success("La migration pour {$name} a été créée");

        // Créer le contrôleur
        $this->createController($name);
        $io->success("Le contrôleur {$name}Controller a été créé");

        // Créer les formulaires
        $this->createForms($name, $fields);
        $io->success("Les formulaires pour {$name} ont été créés");

        // Créer les vues
        $this->createViews($name, $fields);
        $io->success("Les vues pour {$name} ont été créées");

        // Créer les routes
        $this->createRoutes($name);
        $io->success("Les routes pour {$name} ont été créées");

        // Créer les tests
        $this->createTests($name);
        $io->success("Les tests pour {$name} ont été créés");

        return Command::SUCCESS;
    }

    protected function createModel(string $name, array $fields): void
    {
        $fillable = array_map(function ($field) {
            return explode(':', $field)[0];
        }, $fields);

        $modelContent = $this->generateModelContent($name, strtolower($name) . 's', $fillable);
        $modelPath = "src/Models/{$name}.php";

        if (!Filesystem::exists(dirname($modelPath))) {
            Filesystem::makeDirectory(dirname($modelPath), 0755, true);
        }

        Filesystem::put($modelPath, $modelContent);
    }

    protected function createMigration(string $name, array $fields): void
    {
        $timestamp = date('Y_m_d_His');
        $migrationContent = $this->generateMigrationContent("Create{$name}Table", strtolower($name) . 's', $fields);
        $migrationPath = "database/migrations/{$timestamp}_create_{$name}_table.php";

        if (!Filesystem::exists(dirname($migrationPath))) {
            Filesystem::makeDirectory(dirname($migrationPath), 0755, true);
        }

        Filesystem::put($migrationPath, $migrationContent);
    }

    protected function createController(string $name): void
    {
        $controllerContent = $this->generateControllerContent($name);
        $controllerPath = "src/Http/Controllers/{$name}Controller.php";

        if (!Filesystem::exists(dirname($controllerPath))) {
            Filesystem::makeDirectory(dirname($controllerPath), 0755, true);
        }

        Filesystem::put($controllerPath, $controllerContent);
    }

    protected function createForms(string $name, array $fields): void
    {
        $formContent = $this->generateFormContent($name, $fields);
        $formPath = app_path("Forms/{$name}Form.php");

        if (!Filesystem::exists(dirname($formPath))) {
            Filesystem::makeDirectory(dirname($formPath), 0755, true);
        }

        Filesystem::put($formPath, $formContent);
    }

    protected function createViews(string $name, array $fields): void
    {
        $views = [
            'index' => $this->generateIndexView($name, $fields),
            'create' => $this->generateCreateView($name, $fields),
            'edit' => $this->generateEditView($name, $fields),
            'show' => $this->generateShowView($name, $fields)
        ];

        $viewPath = "resources/views/{$name}";
        if (!Filesystem::exists(dirname($viewPath))) {
            Filesystem::makeDirectory(dirname($viewPath), 0755, true);
        }

        foreach ($views as $view => $content) {
            Filesystem::put("{$viewPath}/{$view}.php", $content);
        }
    }

    protected function createRoutes(string $name): void
    {
        $routesContent = $this->generateRoutesContent($name);
        $routesPath = "routes/web.php";

        if (file_exists($routesPath)) {
            $currentRoutes = file_get_contents($routesPath);
            if (strpos($currentRoutes, "Router::resource('{$name}')") === false) {
                Filesystem::put($routesPath, $currentRoutes . "\n" . $routesContent);
            }
        } else {
            Filesystem::put($routesPath, $routesContent);
        }
    }

    protected function createTests(string $name): void
    {
        $testContent = $this->generateTestContent("{$name}Test", 'Feature', "IronFlow\\Models\\{$name}");
        $testPath = "tests/Feature/{$name}Test.php";

        if (!Filesystem::exists(dirname($testPath))) {
            Filesystem::makeDirectory(dirname($testPath), 0755, true);
        }

        Filesystem::put($testPath, $testContent);
    }

    protected function generateModelContent(string $name, string $table, array $fillable): string
    {
        $fillableString = empty($fillable) ? '[]' : "['" . implode("', '", $fillable) . "']";

        return <<<PHP
<?php

namespace IronFlow\Models;

use IronFlow\Database\Model;

class {$name} extends Model
{
    protected string \$table = '{$table}';
    protected array \$fillable = {$fillableString};

    // Méthodes CRUD statiques
    public static function find(int \$id): ?self
    {
        return static::query()->where('id', \$id)->first();
    }

    public static function findOrFail(int \$id): self
    {
        \$model = static::find(\$id);
        if (!\$model) {
            throw new \RuntimeException("{$name} avec l'ID {\$id} non trouvé");
        }
        return \$model;
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function create(array \$data): self
    {
        \$model = new static();
        \$model->fill(\$data);
        \$model->save();
        return \$model;
    }

    public static function update(int \$id, array \$data): bool
    {
        \$model = static::findOrFail(\$id);
        return \$model->update(\$data);
    }

    public static function delete(int \$id): bool
    {
        \$model = static::findOrFail(\$id);
        return \$model->delete();
    }
}
PHP;
    }

    protected function generateMigrationContent(string $name, string $table, array $fields): string
    {
        $upContent = $this->generateUpContent($table, $fields);
        $downContent = $this->generateDownContent($table);

        return <<<PHP
<?php

use IronFlow\Database\Migration;

class {$name} extends Migration
{
    public function up(): void
    {
        {$upContent}
    }

    public function down(): void
    {
        {$downContent}
    }
}
PHP;
    }

    protected function generateControllerContent(string $name): string
    {
        return <<<PHP
<?php

namespace IronFlow\Http\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Models\\{$name};
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class {$name}Controller extends Controller
{
    public function index(): Response
    {
        \${$name}s = {$name}::all();
        return \$this->view('{$name}.index', ['{$name}s' => \${$name}s]);
    }

    public function create(): Response
    {
        return \$this->view('{$name}.create');
    }

    public function store(Request \$request): Response
    {
        \$data = \$request->validate([
            // Ajoutez vos règles de validation ici
        ]);

        {$name}::create(\$data);
        return \$this->redirect()->route('{$name}.index');
    }

    public function show(int \$id): Response
    {
        \${$name} = {$name}::findOrFail(\$id);
        return \$this->view('{$name}.show', ['{$name}' => \${$name}]);
    }

    public function edit(int \$id): Response
    {
        \${$name} = {$name}::findOrFail(\$id);
        return \$this->view('{$name}.edit', ['{$name}' => \${$name}]);
    }

    public function update(Request \$request, int \$id): Response
    {
        \$data = \$request->validate([
            // Ajoutez vos règles de validation ici
        ]);

        {$name}::update(\$id, \$data);
        return \$this->redirect()->route('{$name}.index');
    }

    public function destroy(int \$id): Response
    {
        {$name}::delete(\$id);
        return \$this->redirect()->route('{$name}.index');
    }
}
PHP;
    }

    protected function generateFormContent(string $name, array $fillable): string
    {
        $fieldsContent = $this->generateFieldsContent($fillable);
        $rulesContent = $this->generateValidationRules($fillable);

        return <<<PHP
<?php

namespace App\Components\Forms;

use IronFlow\Furnace\Form;
use IronFlow\Validation\Validator;

class {$name}Form extends Form
{

    public function rules(): array
    {
        return {$rulesContent};
    }

    public function messages(): array
    {
        return [
            // Messages de validation personnalisés
        ];
    }

    public function build(): Form
    {
        {$fieldsContent}

        return \$this;
    }

}
PHP;
    }

    protected function generateFieldsContent(array $fillable): string
    {
        $fieldsContent = [];
        foreach ($fillable as $field) {
            $type = $this->inferFieldType($field);
            $label = ucfirst(str_replace('_', ' ', $field));

            $fieldsContent[] = <<<PHP
        \$this->addField('{$field}', [
            'type' => '{$type}',
            'label' => '{$label}',
            'required' => true
        ]);
PHP;
        }

        return implode("\n        ", $fieldsContent);
    }

    protected function generateValidationRules(array $fillable): string
    {
        $rules = [];
        foreach ($fillable as $field) {
            $rules[$field] = $this->generateFieldRules($field);
        }

        return var_export($rules, true);
    }

    protected function inferFieldType(string $field): string
    {
        // Inférer le type de champ basé sur le nom
        $fieldLower = strtolower($field);

        $typeMap = [
            'email' => 'email',
            'password' => 'password',
            'phone' => 'tel',
            'date' => 'date',
            'time' => 'time',
            'url' => 'url',
            'description' => 'textarea',
            'content' => 'textarea'
        ];

        foreach ($typeMap as $key => $type) {
            if (strpos($fieldLower, $key) !== false) {
                return $type;
            }
        }

        return 'text';
    }

    protected function generateFieldRules(string $field): array
    {
        $rules = ['required'];
        $fieldLower = strtolower($field);

        // Règles spécifiques basées sur le nom du champ
        if (strpos($fieldLower, 'email') !== false) {
            $rules[] = 'email';
            $rules[] = 'max:255';
            $rules[] = 'unique:users,email';
        } elseif (strpos($fieldLower, 'password') !== false) {
            $rules[] = 'min:8';
            $rules[] = 'confirmed';
        } elseif (strpos($fieldLower, 'phone') !== false) {
            $rules[] = 'regex:/^[0-9\-\+]{10,15}$/';
        } elseif (strpos($fieldLower, 'url') !== false) {
            $rules[] = 'url';
        } elseif (strpos($fieldLower, 'date') !== false) {
            $rules[] = 'date';
        }

        return $rules;
    }

    protected function generateIndexView(string $name, array $fields): string
    {
        $headers = array_map(function ($field) {
            return explode(':', $field)[0];
        }, $fields);

        $headersHtml = '';
        foreach ($headers as $header) {
            $headersHtml .= "                <th>" . ucfirst($header) . "</th>\n";
        }

        $rowsHtml = '';
        foreach ($headers as $header) {
            $rowsHtml .= "                    <td><?= \${$name}->{$header} ?></td>\n";
        }

        return <<<PHP
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{$name}s</h1>
        <a href="<?= route('{$name}.create') ?>" class="btn btn-primary">Créer</a>
        
        <table class="table">
            <thead>
                <tr>
                    {$headersHtml}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (\${$name}s as \${$name}): ?>
                <tr>
                    {$rowsHtml}
                    <td>
                        <a href="<?= route('{$name}.show', \${$name}->id) ?>" class="btn btn-info">Voir</a>
                        <a href="<?= route('{$name}.edit', \${$name}->id) ?>" class="btn btn-warning">Modifier</a>
                        <form action="<?= route('{$name}.destroy', \${$name}->id) ?>" method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <?= method_field('DELETE') ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
@endsection
PHP;
    }

    protected function generateCreateView(string $name, array $fields): string
    {
        $formFields = '';
        foreach ($fields as $field) {
            $parts = explode(':', $field);
            $fieldName = $parts[0];
            $fieldType = $parts[1] ?? 'text';
            $formFields .= $this->generateFormField($fieldName, $fieldType);
        }

        return <<<PHP
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Créer {$name}</h1>
        
        <form action="<?= route('{$name}.store') ?>" method="POST">
            <?= csrf_field() ?>
            
            {$formFields}
            
            <button type="submit" class="btn btn-primary">Créer</button>
            <a href="<?= route('{$name}.index') ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
@endsection
PHP;
    }

    protected function generateEditView(string $name, array $fields): string
    {
        $formFields = '';
        foreach ($fields as $field) {
            $parts = explode(':', $field);
            $fieldName = $parts[0];
            $fieldType = $parts[1] ?? 'text';
            $formFields .= $this->generateFormField($fieldName, $fieldType, true);
        }

        return <<<PHP
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Modifier {$name}</h1>
        
        <form action="<?= route('{$name}.update', \${$name}->id) ?>" method="POST">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>
            
            {$formFields}
            
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= route('{$name}.index') ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
@endsection
PHP;
    }

    protected function generateShowView(string $name, array $fields): string
    {
        $fieldsHtml = '';
        foreach ($fields as $field) {
            $fieldName = explode(':', $field)[0];
            $fieldsHtml .= "            <dt>" . ucfirst($fieldName) . "</dt>\n";
            $fieldsHtml .= "            <dd><?= \${$name}->{$fieldName} ?></dd>\n";
        }

        return <<<PHP
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{$name}</h1>
        
        <dl>
            {$fieldsHtml}
        </dl>
        
        <a href="<?= route('{$name}.edit', \${$name}->id) ?>" class="btn btn-warning">Modifier</a>
        <a href="<?= route('{$name}.index') ?>" class="btn btn-secondary">Retour</a>
    </div>
@endsection
PHP;
    }

    protected function generateFormField(string $name, string $type, bool $isEdit = false): string
    {
        $value = $isEdit ? " value=\"<?= \${$name}->{$name} ?>\"" : '';
        $label = ucfirst($name);

        switch ($type) {
            case 'textarea':
                return <<<PHP
            <div class="form-group">
                <label for="{$name}">{$label}</label>
                <textarea name="{$name}" id="{$name}" class="form-control"{$value}></textarea>
            </div>
PHP;
            default:
                return <<<PHP
            <div class="form-group">
                <label for="{$name}">{$label}</label>
                <input type="{$type}" name="{$name}" id="{$name}" class="form-control"{$value}>
            </div>
PHP;
        }
    }

    protected function generateRoutesContent(string $name): string
    {
        return "Router::resource('{$name}', {$name}Controller::class);\n";
    }

    protected function generateTestContent(string $name, string $type, string $class): string
    {
        return <<<PHP
<?php

namespace Tests\\{$type};

use PHPUnit\Framework\TestCase;
use IronFlow\Testing\\{$type}Test;
use {$class};

class {$name} extends {$type}Test
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_list_{$name}s(): void
    {
        \$response = \$this->get(route('{$name}.index'));
        \$response->assertStatus(200);
    }

    public function test_can_create_{$name}(): void
    {
        \$data = [
            // Ajoutez vos données de test ici
        ];

        \$response = \$this->post(route('{$name}.store'), \$data);
        \$response->assertRedirect(route('{$name}.index'));
    }

    public function test_can_update_{$name}(): void
    {
        \${$name} = {$class}::factory()->create();
        \$data = [
            // Ajoutez vos données de test ici
        ];

        \$response = \$this->put(route('{$name}.update', \${$name}->id), \$data);
        \$response->assertRedirect(route('{$name}.index'));
    }

    public function test_can_delete_{$name}(): void
    {
        \${$name} = {$class}::factory()->create();
        \$response = \$this->delete(route('{$name}.destroy', \${$name}->id));
        \$response->assertRedirect(route('{$name}.index'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
PHP;
    }

    protected function generateUpContent(string $table, array $fields): string
    {
        $content = "Schema::create('{$table}', function (\$table) {\n";
        $content .= "            \$table->id();\n";

        foreach ($fields as $field) {
            $parts = explode(':', $field);
            $name = $parts[0];
            $type = $parts[1] ?? 'string';
            $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

            $content .= "            \$table->{$type}('{$name}'";

            if (!empty($options)) {
                $content .= ", " . implode(', ', array_map(function ($option) {
                    return is_numeric($option) ? $option : "'{$option}'";
                }, $options));
            }

            $content .= ");\n";
        }

        $content .= "            \$table->timestamps();\n";
        $content .= "        });";

        return $content;
    }

    protected function generateDownContent(string $table): string
    {
        return "Schema::dropIfExists('{$table}');";
    }
}
