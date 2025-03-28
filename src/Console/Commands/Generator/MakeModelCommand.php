<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelCommand extends Command
{
    protected static $defaultName = 'make:model';
    protected static $defaultDescription = 'Crée un nouveau modèle';

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Le nom du modèle')
            ->addArgument('table', InputArgument::OPTIONAL, 'Le nom de la table')
            ->addArgument('fillable', InputArgument::OPTIONAL, 'Les champs remplissables (séparés par des virgules)')
            ->addOption('migration', 'm', InputOption::VALUE_NONE, 'Créer une migration associée')
            ->addOption('factory', 'f', InputOption::VALUE_NONE, 'Créer une factory associée')
            ->addOption('seeder', 's', InputOption::VALUE_NONE, 'Créer un seeder associé')
            ->addOption('form', 'fm', InputOption::VALUE_NONE, 'Créer un formulaire associé');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $table = $input->getArgument('table') ?? strtolower($name) . 's';
        $fillable = $input->getArgument('fillable') ? explode(',', $input->getArgument('fillable')) : [];
        $withMigration = $input->getOption('migration');
        $withFactory = $input->getOption('factory');
        $withSeeder = $input->getOption('seeder');
        $withForm = $input->getOption('form');

        $modelContent = $this->generateModelContent($name, $table, $fillable, $withFactory, $withForm);
        $modelPath = app_path("Models/") . "{$name}.php";

        if (!is_dir(dirname($modelPath))) {
            mkdir(dirname($modelPath), 0755, true);
        }

        file_put_contents($modelPath, $modelContent);
        $io->success("Le modèle {$name} a été créé avec succès !");

        if ($withMigration) {
            $migrationName = 'create_' . $table . '_table';
            $this->createMigration($io, $migrationName, $table, $fillable);
        }

        if ($withFactory) {
            $this->createFactory($io, $name, $fillable);
        }

        if ($withSeeder) {
            $this->createSeeder($io, $name);
        }

        if ($withForm) {
            $this->createForm($io, $name, $fillable);
        }

        return Command::SUCCESS;
    }

    protected function generateModelContent(string $name, string $table, array $fillable, bool $hasFactory, bool $hasForm): string
    {
        $fillableContent = empty($fillable) ? '    protected $fillable = [];' : "    protected \$fillable = [\n        '" . implode("',\n        '", $fillable) . "'\n    ];";
        $hasFactoryImport = $hasFactory ? 'use IronFlow\Database\Factories\HasFactory;' : "";
        $hasFormImport = $hasForm ? 'use IronFlow\Forms\HasForm;' : "";
        $hasFactoryContent = $hasFactory ? 'use HasFactory;' : "";
        $hasFormContent = $hasFactory ? 'use HasForm;' : "";

        return <<<PHP
<?php

namespace App\Models;

{$hasFactoryImport}
{$hasFormImport}
use IronFlow\Database\Model;

class {$name} extends Model
{
    {$hasFactoryContent}
    {$hasFormContent}
    protected \$table = '{$table}';

{$fillableContent}

    protected \$casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
PHP;
    }

    protected function createMigration(SymfonyStyle $io, string $name, string $table, array $fillable): void
    {
        $timestamp = date('Y_m_d_His');
        $migrationPath = database_path("Migrations/") . "{$timestamp}_{$name}.php";

        $migrationContent = $this->generateMigrationContent($table, $fillable);
        file_put_contents($migrationPath, $migrationContent);
        $io->success("La migration {$name} a été créée avec succès !");
    }

    protected function generateMigrationContent(string $table, array $fillable): string
    {
        $columns = [];
        foreach ($fillable as $field) {
            $columns[] = "            \$table->string('{$field}');";
        }

        $columnsContent = empty($columns) ? "            \$table->id();\n            \$table->timestamps();" : implode("\n", $columns);

        return <<<PHP
<?php

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use Ironflow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createTable('{$table}', function (Anvil \$table) {
{$columnsContent}
        });
    }

    public function down(): void
    {
        Schema::dropTableIfExists('{$table}');
    }
};
PHP;
    }

    protected function createFactory(SymfonyStyle $io, string $name, array $fillable): void
    {
        $factoryPath = database_path("Factories/") . "{$name}Factory.php";

        $factoryContent = $this->generateFactoryContent($name, $fillable);
        file_put_contents($factoryPath, $factoryContent);
        $io->success("La factory {$name} a été créée avec succès !");
    }

    protected function generateFactoryContent(string $name, array $fillable): string
    {
        $fakerContent = [];
        foreach ($fillable as $field) {
            $fakerContent[] = "            '{$field}' => \$fake->word,";
        }

        $fakerContent = empty($fakerContent) ? "            'name' => \$fake->word," : implode("\n", $fakerContent);

        return <<<PHP
<?php

namespace Database\Factories;

use IronFlow\Database\Factories\Factory;
use App\Models\\{$name};
use Faker\Generator as FakerGenerator;

class {$name}Factory extends Factory
{
    public function definition(FakerGenerator \$fake): array
    {
        return [
{$fakerContent}
        ];
    }
}
PHP;
    }

    protected function createSeeder(SymfonyStyle $io, string $name): void
    {
        $seederPath = database_path("Seeders/") . "{$name}Seeder.php";

        $seederContent = $this->generateSeederContent($name);
        file_put_contents($seederPath, $seederContent);
        $io->success("Le seeder {$name} a été créé avec succès !");
    }

    protected function generateSeederContent(string $name): string
    {
        return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\\{$name};
use Database\Factories\\{$name}Factory;

class {$name}Seeder
{
    public function run(): void
    {
        {$name}::factory(10)->create();
    }
}
PHP;
    }

    protected function createForm(SymfonyStyle $io, string $name, array $fillable): void
    {
        $formPath = app_path("Components/Forms") . "{$name}Form.php";

        $formContent = $this->generateFormContent($name, $fillable);
        file_put_contents($formPath, $formContent);
        $io->success("Le formulaire {$name} a été créé avec succès !");
    }

    protected function generateFormContent(string $name, array $fillable): string
    {
        $fieldsContent = $this->generateFieldsContent($fillable);
        $rulesContent = $this->generateValidationRules($fillable);

        return <<<PHP
<?php

namespace App\Components\Forms;

use IronFlow\Forms\Form;
use IronFlow\Validation\Validator;

class {$name}Form extends Form
{
    public function __construct()
    {
        parent::__construct();
        
        {$fieldsContent}
    }

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
}
