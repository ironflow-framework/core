<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFormCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:form')
            ->setDescription('Create a new form class')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the form')
            ->addArgument('fields', InputArgument::OPTIONAL, 'Form fields (format: name:type:options)')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Custom namespace', 'App\\Forms')
            ->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'Associate with a model', null)
            ->addOption('crud', 'c', InputOption::VALUE_NONE, 'Generate CRUD form with model fields');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');
        $fieldsInput = $input->getArgument('fields');
        $namespace = $input->getArgument('namespace');
        $modelName = $input->getOption('model');
        $isCrud = $input->getOption('crud');

        $formClassName = str_replace(['Form', 'form'], '', $name) . 'Form';

        $fields = $fieldsInput ? $this->parseFields($fieldsInput) : [];

        if ($modelName) {
            $io->text("Association avec le modèle: {$modelName}");
            $modelFields = $this->getModelFields($modelName);

            if (!empty($modelFields)) {
                if (empty($fields) || $isCrud) {
                    $fields = $modelFields;
                    $io->text("Utilisation des champs du modèle: " . implode(', ', array_column($modelFields, 'name')));
                }
            } else {
                $io->warning("Aucun champ trouvé pour le modèle {$modelName}. Utilisation des champs fournis.");
            }

            $formContent = $this->generateModelFormContent($namespace, $formClassName, $fields, $modelName, $isCrud);
        } else {
            $formContent = $this->generateFormContent($namespace, $formClassName, $fields);
        }

        $basePath = str_replace('\\', '/', $namespace);
        $formPath = base_path($basePath . '/' . $formClassName . '.php');

        if (!Filesystem::exists(dirname($formPath))) {
            Filesystem::makeDirectory(dirname($formPath), 0755, true);
        }

        Filesystem::put($formPath, $formContent);

        $io->success("Form {$formClassName} created successfully at {$formPath}");

        return Command::SUCCESS;
    }

    protected function parseFields(?string $fieldsInput): array
    {
        if (empty($fieldsInput)) {
            return [];
        }

        $parsedFields = [];
        $fieldEntries = explode(',', $fieldsInput);

        foreach ($fieldEntries as $entry) {
            $parts = explode(':', $entry);

            $field = [
                'name' => $parts[0] ?? '',
                'type' => $parts[1] ?? 'text',
                'options' => []
            ];

            // Parse additional options if exists
            if (isset($parts[2])) {
                $optionParts = explode(';', $parts[2]);
                foreach ($optionParts as $option) {
                    $optionDetail = explode('=', $option);
                    if (count($optionDetail) === 2) {
                        $key = trim($optionDetail[0]);
                        $value = trim($optionDetail[1]);

                        // Convert string values to appropriate types
                        if (strtolower($value) === 'true') {
                            $value = true;
                        } elseif (strtolower($value) === 'false') {
                            $value = false;
                        } elseif (is_numeric($value)) {
                            $value = strpos($value, '.') !== false ? floatval($value) : intval($value);
                        }

                        $field['options'][$key] = $value;
                    }
                }
            }

            $parsedFields[] = $field;
        }

        return $parsedFields;
    }

    protected function getModelFields(string $modelName): array
    {
        // Chemin complet du modèle
        $modelNamespace = "App\\Models\\{$modelName}";
        $modelPath = app_path("Models/{$modelName}.php");

        if (!Filesystem::exists($modelPath)) {
            return [];
        }

        // On lit le contenu du fichier
        $content = Filesystem::get($modelPath);

        // On extrait les champs fillable du modèle
        if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
            $fillableString = $matches[1];
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $fillableString, $fieldMatches);

            $fields = [];
            foreach ($fieldMatches[1] as $fieldName) {
                $fields[] = [
                    'name' => $fieldName,
                    'type' => $this->inferFieldType($fieldName),
                    'options' => []
                ];
            }

            return $fields;
        }

        return [];
    }

    protected function inferFieldType(string $fieldName): string
    {
        // Logique pour déduire le type de champ à partir du nom
        $lowerName = strtolower($fieldName);

        if (str_contains($lowerName, 'email')) {
            return 'email';
        } elseif (str_contains($lowerName, 'password')) {
            return 'password';
        } elseif (str_contains($lowerName, 'date') || str_contains($lowerName, 'time')) {
            return 'date';
        } elseif (str_contains($lowerName, 'image') || str_contains($lowerName, 'photo') || str_contains($lowerName, 'avatar')) {
            return 'file';
        } elseif (str_contains($lowerName, 'description') || str_contains($lowerName, 'content')) {
            return 'textarea';
        } elseif (str_contains($lowerName, 'active') || str_contains($lowerName, 'enabled') || str_contains($lowerName, 'status')) {
            return 'checkbox';
        } elseif (str_contains($lowerName, 'price') || str_contains($lowerName, 'amount')) {
            return 'number';
        } elseif (str_contains($lowerName, 'color')) {
            return 'color';
        } elseif (str_contains($lowerName, 'url') || str_contains($lowerName, 'link') || str_contains($lowerName, 'website')) {
            return 'url';
        }

        return 'text';
    }

    protected function generateFormContent(string $namespace, string $formClassName, array $fields): string
    {
        $fieldsContent = $this->generateFieldsContent($fields);

        return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Furnace\Form;

class {$formClassName} extends Form
{
    protected function build(): void
    {
{$fieldsContent}
    }
}
PHP;
    }

    protected function generateModelFormContent(string $namespace, string $formClassName, array $fields, string $modelName, bool $isCrud): string
    {
        $fieldsContent = $this->generateFieldsContent($fields);
        $validationContent = $this->generateValidationContent($fields);

        $modelNs = "App\\Models\\{$modelName}";

        // Génére un formulaire de base associé à un modèle
        if (!$isCrud) {
            return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Furnace\Form;
use {$modelNs};

class {$formClassName} extends Form
{
    /**
     * Modèle associé au formulaire
     */
    protected ?string \$model = {$modelName}::class;

    protected function build(): void
    {
{$fieldsContent}
    }
    
    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return {$validationContent};
    }
}
PHP;
        }

        // Génére un formulaire CRUD complet
        return <<<PHP
<?php

namespace {$namespace};

use IronFlow\Furnace\Form;
use {$modelNs};

class {$formClassName} extends Form
{
    /**
     * Modèle associé au formulaire
     */
    protected ?string \$model = {$modelName}::class;
    
    /**
     * Indique si c'est un formulaire de création ou d'édition
     */
    protected bool \$isUpdate = false;
    
    /**
     * ID de l'enregistrement en cours d'édition
     */
    protected ?int \$recordId = null;
    
    /**
     * Définir le mode édition
     */
    public function setUpdateMode(int \$id): self
    {
        \$this->isUpdate = true;
        \$this->recordId = \$id;
        return \$this;
    }

    protected function build(): void
    {
{$fieldsContent}

        // Ajouter les boutons de soumission
        \$this->addField('submit', 'submit', [
            'label' => \$this->isUpdate ? 'Mettre à jour' : 'Créer',
            'class' => 'btn btn-primary'
        ]);
        
        if (\$this->isUpdate) {
            \$this->addField('delete', 'button', [
                'label' => 'Supprimer',
                'class' => 'btn btn-danger',
                'type' => 'button',
                'attributes' => [
                    'onclick' => 'confirmDelete()'
                ]
            ]);
        }
    }
    
    /**
     * Règles de validation
     */
    public function rules(): array
    {
        \$rules = {$validationContent};
        
        // Adapter les règles pour l'édition
        if (\$this->isUpdate && \$this->recordId) {
            // Exemple: unicité avec exception pour l'enregistrement actuel
            // \$rules['email'] = ['required', 'email', 'unique:users,email,' . \$this->recordId];
        }
        
        return \$rules;
    }
    
    /**
     * Charger les données depuis le modèle
     */
    public function loadModel(int \$id): self
    {
        \$model = {$modelName}::find(\$id);
        
        if (\$model) {
            \$this->setValues(\$model->toArray());
            \$this->setUpdateMode(\$id);
        }
        
        return \$this;
    }
    
    /**
     * Enregistrer le formulaire dans le modèle
     */
    public function save(): ?{$modelName}
    {
        if (!empty(\$this->errors)) {
            return null;
        }
        
        \$data = \$this->getValues();
        
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

    protected function generateFieldsContent(array $fields): string
    {
        $content = '';
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $options = $field['options'];

            // Ajout d'un label par défaut s'il n'existe pas
            if (!isset($options['label'])) {
                $options['label'] = ucfirst(str_replace('_', ' ', $name));
            }

            // Convert options to a string representation
            $optionsStr = $this->formatOptions($options);

            $content .= "        \$this->addField('{$name}', '{$type}', {$optionsStr});\n";
        }
        return $content;
    }

    protected function formatOptions(array $options): string
    {
        if (empty($options)) {
            return '[]';
        }

        $formattedOptions = [];
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                $formattedOptions[] = "'{$key}' => " . ($value ? 'true' : 'false');
            } elseif (is_string($value)) {
                $formattedOptions[] = "'{$key}' => '{$value}'";
            } else {
                $formattedOptions[] = "'{$key}' => {$value}";
            }
        }

        return "[\n            " . implode(",\n            ", $formattedOptions) . "\n        ]";
    }

    protected function generateValidationContent(array $fields): string
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];

            $fieldRules = $this->getFieldValidationRules($name, $type);

            if (!empty($fieldRules)) {
                $rules[] = "            '{$name}' => ['" . implode("', '", $fieldRules) . "']";
            }
        }

        if (empty($rules)) {
            return '[]';
        }

        return "[\n" . implode(",\n", $rules) . "\n        ]";
    }

    protected function getFieldValidationRules(string $name, string $type): array
    {
        $rules = ['required'];

        switch ($type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'file':
                $rules = ['file'];
                break;
            case 'checkbox':
                $rules = ['boolean'];
                break;
            case 'password':
                $rules[] = 'min:8';
                break;
        }

        // Ajouter des règles spécifiques selon le nom du champ
        if (str_contains($name, 'email')) {
            if (!in_array('email', $rules)) {
                $rules[] = 'email';
            }
            $rules[] = 'unique:users,email';
        } elseif ($name === 'name' || $name === 'title') {
            $rules[] = 'max:255';
        }

        return $rules;
    }
}
