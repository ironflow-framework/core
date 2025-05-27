<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use IronFlow\Support\Facades\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFormCommand extends Command
{
    protected static $defaultName = 'make:form';
    protected static $defaultDescription = 'Crée une nouvelle classe de formulaire';

    protected function configure()
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Nom du formulaire')
            ->addArgument('fillable', InputArgument::OPTIONAL, 'Champs du formulaire (séparés par des virgules)')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Namespace personnalisé', 'App\\Forms')
            ->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'Associer avec un modèle', null)
            ->addOption('crud', 'c', InputOption::VALUE_NONE, 'Générer un formulaire CRUD avec les champs du modèle')
            ->addOption('theme', 't', InputOption::VALUE_OPTIONAL, 'Thème du formulaire (default, floating, material, tailwind)', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');
        $fillableInput = $input->getArgument('fillable');
        $namespace = $input->getArgument('namespace');
        $modelName = $input->getOption('model');
        $isCrud = $input->getOption('crud');
        $theme = $input->getOption('theme');

        $formClassName = Str::studly(str_replace(['Form', 'form'], '', $name)) . 'Form';

        $fillable = $fillableInput ? explode(',', $fillableInput) : [];

        if ($modelName) {
            $io->text("Association avec le modèle: {$modelName}");
            $modelFields = $this->getModelFields($modelName);

            if (!empty($modelFields)) {
                if (empty($fillable) || $isCrud) {
                    $fillable = $modelFields;
                    $io->text("Utilisation des champs du modèle: " . implode(', ', $modelFields));
                }
            } else {
                $io->warning("Aucun champ trouvé pour le modèle {$modelName}. Utilisation des champs fournis.");
            }

            $formContent = $this->generateModelFormContent($namespace, $formClassName, $fillable, $modelName, $isCrud, $theme);
        } else {
            $formContent = $this->generateFormContent($namespace, $formClassName, $fillable, $theme);
        }

        $basePath = str_replace('\\', '/', $namespace);
        $formPath = base_path($basePath . '/' . $formClassName . '.php');

        if (!Filesystem::exists(dirname($formPath))) {
            Filesystem::makeDirectory(dirname($formPath), 0755, true);
        }

        Filesystem::put($formPath, $formContent);

        $io->success("Formulaire {$formClassName} créé avec succès à {$formPath}");

        return Command::SUCCESS;
    }

    protected function getModelFields(string $modelName): array
    {
        $modelPath = app_path("Models/{$modelName}.php");

        if (!Filesystem::exists($modelPath)) {
            return [];
        }

        $content = Filesystem::get($modelPath);

        if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
            $fillableString = $matches[1];
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $fillableString, $fieldMatches);
            return $fieldMatches[1];
        }

        return [];
    }

    protected function generateFormContent(string $namespace, string $formClassName, array $fillable, string $theme): string
    {
        $fieldsContent = $this->generateFieldsContent($fillable);
        $modelNameLower = strtolower(str_replace('Form', '', $formClassName));

        return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Forms\Form;

class {$formClassName} extends Form
{
    public function __construct(?string \$model = null)
    {
        parent::__construct(\$model);

        \$this->title("{$formClassName}")
            ->theme('{$theme}')
            ->action(route('{$modelNameLower}.store'))
            ->method('POST');

{$fieldsContent}

        \$this->button('Enregistrer', ['type' => 'submit', 'class' => 'btn btn-primary']);
    }
}
PHP;
    }

    protected function generateModelFormContent(string $namespace, string $formClassName, array $fillable, string $modelName, bool $isCrud, string $theme): string
    {
        $fieldsContent = $this->generateFieldsContent($fillable);
        $modelNs = "App\\Models\\{$modelName}";
        $modelNameLower = strtolower($modelName);

        if (!$isCrud) {
            return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Forms\Form;
use {$modelNs};

class {$formClassName} extends Form
{
    public function __construct(?string \$model = null)
    {
        parent::__construct(\$model ?? {$modelName}::class);

        \$this->title("{$modelName} Formulaire")
            ->theme('{$theme}')
            ->action(route('{$modelNameLower}.store'))
            ->method('POST');

{$fieldsContent}

        \$this->button('Enregistrer', ['type' => 'submit', 'class' => 'btn btn-primary']);
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Forms\Form;
use {$modelNs};

class {$formClassName} extends Form
{
    protected bool \$isUpdate = false;
    protected ?int \$recordId = null;

    public function __construct(?string \$model = null)
    {
        parent::__construct(\$model ?? {$modelName}::class);

        \$this->title("{$modelName} Formulaire")
            ->theme('{$theme}');

        \$this->buildForm();
    }

    /**
     * Définir le mode édition
     */
    public function setUpdateMode(int \$id): self
    {
        \$this->isUpdate = true;
        \$this->recordId = \$id;
        
        \$this->action(route('{$modelNameLower}.update', \$id))
            ->method('PUT')
            ->whenEditing(\$id);

        return \$this;
    }

    /**
     * Construire le formulaire
     */
    protected function buildForm(): void
    {
{$fieldsContent}

        // Boutons de soumission
        \$submitLabel = \$this->isUpdate ? 'Mettre à jour' : 'Créer';
        \$this->button(\$submitLabel, ['type' => 'submit', 'class' => 'btn btn-primary']);

        if (\$this->isUpdate) {
            \$this->button('Supprimer', [
                'type' => 'button',
                'class' => 'btn btn-danger',
                'onclick' => 'confirmDelete()'
            ]);
        }
    }

    /**
     * Charger les données depuis le modèle
     */
    public function loadModel(int \$id): self
    {
        \$model = {$modelName}::find(\$id);

        if (\$model) {
            \$this->fill(\$model->toArray());
            \$this->setUpdateMode(\$id);
        }

        return \$this;
    }

    /**
     * Enregistrer le formulaire dans le modèle
     */
    public function save(array \$data): ?{$modelName}
    {
        if (\$this->isUpdate && \$this->recordId) {
            \$model = {$modelName}::find(\$this->recordId);
            if (\$model) {
                \$model->fill(\$data);
                \$model->save();
                return \$model;
            }
            return null;
        }

        return {$modelName}::create(\$data);
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
            $field = trim($field);
            $type = $this->inferFieldType($field);
            $label = ucfirst(str_replace('_', ' ', $field));

            return match ($type) {
                'email' => "        \$this->input('{$field}', '{$label}', ['type' => 'email', 'placeholder' => 'exemple@mail.com']);",
                'password' => "        \$this->input('{$field}', '{$label}', ['type' => 'password']);",
                'number' => "        \$this->input('{$field}', '{$label}', ['type' => 'number']);",
                'tel' => "        \$this->input('{$field}', '{$label}', ['type' => 'tel']);",
                'url' => "        \$this->input('{$field}', '{$label}', ['type' => 'url']);",
                'color' => "        \$this->color('{$field}', '{$label}');",
                'date' => "        \$this->date('{$field}', '{$label}');",
                'file' => "        \$this->file('{$field}', '{$label}');",
                'textarea' => "        \$this->textarea('{$field}', '{$label}');",
                'checkbox' => "        \$this->checkbox('{$field}', '{$label}');",
                default => "        \$this->input('{$field}', '{$label}');"
            };
        }, $fillable);

        return implode("\n", $fieldsContent);
    }

    protected function inferFieldType(string $field): string
    {
        $lowerField = strtolower($field);

        $typeMap = [
            'email' => 'email',
            'password' => 'password',
            'price' => 'number',
            'amount' => 'number',
            'quantity' => 'number',
            'age' => 'number',
            'phone' => 'tel',
            'tel' => 'tel',
            'mobile' => 'tel',
            'date' => 'date',
            'time' => 'date',
            'url' => 'url',
            'link' => 'url',
            'website' => 'url',
            'color' => 'color',
            'image' => 'file',
            'photo' => 'file',
            'avatar' => 'file',
            'file' => 'file',
            'document' => 'file',
            'description' => 'textarea',
            'content' => 'textarea',
            'message' => 'textarea',
            'comment' => 'textarea',
            'active' => 'checkbox',
            'enabled' => 'checkbox',
            'status' => 'checkbox',
            'published' => 'checkbox'
        ];

        foreach ($typeMap as $key => $type) {
            if (str_contains($lowerField, $key)) {
                return $type;
            }
        }

        return 'text';
    }
}
