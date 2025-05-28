<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Facades\Str;
use IronFlow\Support\Facades\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelCommand extends Command
{
    protected static $defaultName        = 'make:model';
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
            ->addOption('form', 'i', InputOption::VALUE_NONE, 'Créer un formulaire associé');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io          = new SymfonyStyle($input, $output);
        $name        = $input->getArgument('name');
        $table       = $input->getArgument('table') ?? strtolower($name) . 's';
        $fillable    = $input->getArgument('fillable') ? explode(',', $input->getArgument('fillable')) : [];
        $withMigration = $input->getOption('migration');
        $withFactory   = $input->getOption('factory');
        $withSeeder    = $input->getOption('seeder');
        $withForm      = $input->getOption('form');

        $modelName = Str::studly($name);
        $modelPath = app_path("Models/{$modelName}.php");

        $modelContent = $this->generateModelContent($modelName, $table, $fillable, $withFactory, $withForm);

        if (!Filesystem::isDirectory(dirname($modelPath))) {
            Filesystem::makeDirectory(dirname($modelPath), 0755, true);
        }

        Filesystem::put($modelPath, $modelContent);
        $io->success("Le modèle {$modelName} a été créé avec succès !");

        if ($withMigration) {
            $this->createMigration($io, 'create_' . $table . '_table', $table, $fillable);
        }

        if ($withFactory) {
            $this->createFactory($io, $modelName, $fillable);
        }

        if ($withSeeder) {
            $this->createSeeder($io, $modelName);
        }

        if ($withForm) {
            $this->createForm($io, $modelName, $fillable);
        }

        return Command::SUCCESS;
    }

    protected function generateModelContent(string $name, string $table, array $fillable, bool $hasFactory, bool $hasForm): string
    {
        $fillableContent   = empty($fillable)
            ? "    protected array \$fillable = [];"
            : "    protected array \$fillable = [\n        '" . implode("',\n        '", $fillable) . "'\n    ];";

        $traits = [];
        $imports = [];

        if ($hasFactory) {
            $imports[] = "use IronFlow\Database\Traits\HasFactory;";
            $traits[]  = "use HasFactory;";
        }

        if ($hasForm) {
            $imports[] = "use IronFlow\Database\Traits\HasForm;";
            $traits[]  = "use HasForm;";
        }

        $importLines = implode("\n", $imports);
        $traitLines  = implode("\n    ", $traits);

        return <<<PHP
<?php

namespace App\Models;

{$importLines}
use IronFlow\Database\Model;

class {$name} extends Model
{
    {$traitLines}

    protected static string \$table = '{$table}';

{$fillableContent}

    protected array \$casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
PHP;
    }

    protected function createMigration(SymfonyStyle $io, string $name, string $table, array $fillable): void
    {
        $timestamp       = date('Y_m_d_His');
        $migrationPath   = database_path("Migrations/{$timestamp}_{$name}.php");
        $migrationContent = $this->generateMigrationContent($table, $fillable);

        if (!Filesystem::isDirectory(dirname($migrationPath))) {
            Filesystem::makeDirectory(dirname($migrationPath));
        }

        Filesystem::put($migrationPath, $migrationContent);
        $io->success("La migration {$timestamp}_{$name} a été créée avec succès !");
    }

    protected function generateMigrationContent(string $table, array $fillable): string
    {
        $columns = array_map(fn($field) => "            \$table->string('{$field}');", $fillable);

        if (empty($columns)) {
            $columns[] = "            \$table->id();";
        }
        $columns[] = "            \$table->timestamps();";

        $columnsContent = implode("\n", $columns);

        return <<<PHP
<?php

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
        $factoryPath     = database_path("Factories/{$name}Factory.php");
        $factoryContent  = $this->generateFactoryContent($name, $fillable);


        if (!Filesystem::isDirectory(dirname($factoryPath))) {
            Filesystem::makeDirectory(dirname($factoryPath));
        }
        Filesystem::put($factoryPath, $factoryContent);
        $io->success("La factory {$name} a été créée avec succès !");
    }

    protected function generateFactoryContent(string $name, array $fillable): string
    {
        $fakerFields = array_map(fn($field) => "            '{$field}' => \$this->fake->word,", $fillable);
        if (empty($fakerFields)) {
            $fakerFields[] = "            'name' => \$this->fake->word,";
        }
        $fakerContent = implode("\n", $fakerFields);

        return <<<PHP
<?php

use IronFlow\Database\Factories\Factory;
use App\Models\\{$name};

class {$name}Factory extends Factory
{
    protected string \$model = {$name}::class;

    protected function configure(): void
    {
        \$this->states = [];
    }

    public function definition(): array
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
        $seederPath     = database_path("Seeders/{$name}Seeder.php");
        $seederContent  = $this->generateSeederContent($name);


        if (!Filesystem::isDirectory(dirname($seederPath))) {
            Filesystem::makeDirectory(dirname($seederPath));
        }

        Filesystem::put($seederPath, $seederContent);
        $io->success("Le seeder {$name}Seeder a été créé avec succès !");
    }

    protected function generateSeederContent(string $name): string
    {
        return <<<PHP
<?php

use App\Models\\{$name};
use Database\Factories\\{$name}Factory;

class {$name}Seeder
{
    public function run(): void
    {
        // Seed 5 records
        {$name}::factory(5)->create();

        \$factory = new {$name}Factory();
        \$factory->createMany(5);
    }
}
PHP;
    }

    protected function createForm(SymfonyStyle $io, string $name, array $fillable): void
    {
        $formPath     = app_path("Forms/{$name}Form.php");
        $formContent  = $this->generateFormContent($name, $fillable);


        if (!Filesystem::isDirectory(dirname($formPath))) {
            Filesystem::makeDirectory(dirname($formPath));
        }

        Filesystem::put($formPath, $formContent);
        $io->success("La classe {$name}Form a été créée avec succès !");
    }

    protected function generateFormContent(string $name, array $fillable): string
    {
        $fieldsContent = $this->generateFieldsContent($fillable);

        return <<<PHP
<?php

namespace App\Components\Forms;

use App\Models\\{$name};
use IronFlow\Forms\Form;

class {$name}Form extends Form
{
    public function __construct(?string \$name = null)
    {
        parent::__construct({$name});

        \$this->title("{$name} Formulaire")
            ->action(route('{$name}.store'))
            ->button('Enregistrer');

{$fieldsContent}

    }
}
PHP;
    }


    protected function generateFieldsContent(array $fillable): string
    {
        if (empty($fillable)) {
            return "        // Aucun champ défini pour ce formulaire.";
        }

        $fieldsContent = array_map(function ($field) {
            $type  = $this->inferFieldType($field);
            $label = ucfirst(str_replace('_', ' ', $field));

            return match ($type) {
                'email'     => "        \$this->input('{$field}', '{$label}', ['type' => 'email', 'placeholder' => 'exemple@mail.com']);",
                'password'  => "        \$this->input('{$field}', '{$label}', ['type' => 'password']);",
                'number'    => "        \$this->input('{$field}', '{$label}')->type('number');",
                'tel'       => "        \$this->input('{$field}', '{$label}')->type('tel');",
                'date'      => "        \$this->date('{$field}', '{$label}');",
                'textarea'  => "        \$this->textarea('{$field}', '{$label}');",
                default     => "        \$this->input('{$field}', '{$label}');"
            };
        }, $fillable);

        return implode("\n", $fieldsContent);
    }


    protected function inferFieldType(string $field): string
    {
        $typeMap = [
            'email'       => 'email',
            'password'    => 'password',
            'price'       => 'number',
            'slug'        => 'text',
            'phone'       => 'tel',
            'date'        => 'date',
            'time'        => 'time',
            'url'         => 'url',
            'description' => 'textarea',
            'content'     => 'textarea'
        ];

        foreach ($typeMap as $key => $type) {
            if (str_contains(strtolower($field), $key)) {
                return $type;
            }
        }

        return 'text';
    }
}
