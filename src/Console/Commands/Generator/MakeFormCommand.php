<?php

namespace IronFlow\Furnace\Console\Commands;

use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Custom namespace', 'App\\Forms');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $name = $input->getArgument('name');
        $fieldsInput = $input->getArgument('fields');
        $namespace = $input->getArgument('namespace');

        $formClassName = str_replace(['Form', 'form'], '', $name) . 'Form';
        
        $fields = $fieldsInput ? $this->parseFields($fieldsInput) : [];

        $formContent = $this->generateFormContent($namespace, $formClassName, $fields);

        $basePath = str_replace('\\', '/', $namespace);
        $formPath = base_path($basePath . '/' . $formClassName . '.php');

        if (!Filesystem::exists($formPath)) {
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

    protected function generateFieldsContent(array $fields): string
    {
        $content = '';
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $options = $field['options'];

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
}
