<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Facades\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Facades\Str;

class MakeComponentCommand extends Command
{
   protected static $defaultName = 'make:component';
   protected static $defaultDescription = 'Crée un nouveau composant avec support avancé des props';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du composant')
         ->addOption('props', 'p', InputOption::VALUE_OPTIONAL, 'Les propriétés (format: nom:type:required,nom2:type)')
         ->addOption('validation', null, InputOption::VALUE_NONE, 'Ajouter des règles de validation avancées')
         ->addOption('slots', 's', InputOption::VALUE_NONE, 'Ajouter le support des slots')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Écraser le fichier existant');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = Str::studly($input->getArgument('name'));
      $propsString = $input->getOption('props');
      $withValidation = $input->getOption('validation');
      $withSlots = $input->getOption('slots');
      $force = $input->getOption('force');

      $props = $propsString ? $this->parseProps($propsString) : [];

      $componentContent = $this->generateComponentContent($name, $props, $withValidation, $withSlots);
      $componentPath = app_path("Components/{$name}Component.php");

      $templateContent = $this->generateTemplateContent($name, $props, $withSlots);
      $componentTemplatePath = view_path("templates/" . strtolower($name) . ".twig");

      // Vérification de l'existence des fichiers
      if (!$force && (Filesystem::exists($componentPath) || Filesystem::exists($componentTemplatePath))) {
         if (!$io->confirm('Les fichiers existent déjà. Voulez-vous les écraser ?')) {
            $io->info('Génération annulée.');
            return Command::SUCCESS;
         }
      }

      // Création des répertoires si nécessaire
      if (!Filesystem::isDirectory(dirname($componentPath))) {
         Filesystem::makeDirectory(dirname($componentPath), 0755, true);
      }

      if (!Filesystem::isDirectory(dirname($componentTemplatePath))) {
         Filesystem::makeDirectory(dirname($componentTemplatePath), 0755, true);
      }

      // Écriture des fichiers
      Filesystem::put($componentPath, $componentContent);
      Filesystem::put($componentTemplatePath, $templateContent);

      $io->success("Le composant {$name}Component a été créé avec succès !");
      $io->table(['Fichier', 'Chemin'], [
         ['Composant', $componentPath],
         ['Template', $componentTemplatePath]
      ]);

      return Command::SUCCESS;
   }

   protected function parseProps(string $propsString): array
   {
      $props = [];
      $propDefinitions = explode(',', $propsString);

      foreach ($propDefinitions as $propDef) {
         $parts = explode(':', trim($propDef));
         $name = $parts[0];
         $type = $parts[1] ?? 'mixed';
         $required = isset($parts[2]) && $parts[2] === 'required';

         $props[] = [
            'name' => $name,
            'type' => $type,
            'required' => $required
         ];
      }

      return $props;
   }

   protected function generateComponentContent(string $name, array $props, bool $withValidation, bool $withSlots): string
   {
      $defaultsMethod = $this->generateDefaultsMethod($props);
      $rulesMethod = $withValidation ? $this->generateRulesMethod($props) : '';
      $renderMethod = $this->generateRenderMethod($name, $props, $withSlots);

      return <<<PHP
<?php

declare(strict_types=1);

namespace App\Components;

use IronFlow\Components\BaseComponent;

class {$name}Component extends BaseComponent
{
{$defaultsMethod}
{$rulesMethod}
{$renderMethod}
}
PHP;
   }

   protected function generateDefaultsMethod(array $props): string
   {
      if (empty($props)) {
         return '';
      }

      $defaults = "    protected function defaults(): array\n    {\n        return [\n";

      foreach ($props as $prop) {
         $defaultValue = $this->getDefaultValueForType($prop['type']);
         $defaults .= "            '{$prop['name']}' => {$defaultValue},\n";
      }

      $defaults .= "        ];\n    }\n";

      return $defaults;
   }

   protected function generateRulesMethod(array $props): string
   {
      if (empty($props)) {
         return '';
      }

      $rules = "\n    protected function rules(): array\n    {\n        return [\n";

      foreach ($props as $prop) {
         $ruleArray = [];

         if ($prop['required']) {
            $ruleArray[] = "'required' => true";
         }

         if ($prop['type'] !== 'mixed') {
            $ruleArray[] = "'type' => '{$prop['type']}'";
         }

         if (!empty($ruleArray)) {
            $ruleString = '[' . implode(', ', $ruleArray) . ']';
            $rules .= "            '{$prop['name']}' => {$ruleString},\n";
         }
      }

      $rules .= "        ];\n    }\n";

      return $rules;
   }

   protected function generateRenderMethod(string $name, array $props, bool $withSlots): string
   {
      $templateName = strtolower($name);
      $templateData = '[]';

      if (!empty($props) || $withSlots) {
         $dataItems = [];

         foreach ($props as $prop) {
            $dataItems[] = "            '{$prop['name']}' => \$this->prop('{$prop['name']}')";
         }

         if ($withSlots) {
            $dataItems[] = "            // Slots disponibles automatiquement via \$this->slots";
         }

         if (!empty($dataItems)) {
            $templateData = "[\n" . implode(",\n", $dataItems) . "\n        ]";
         }
      }

      return <<<PHP

    public function render(): string
    {
        return \$this->view('{$templateName}', {$templateData});
    }
PHP;
   }

   protected function generateTemplateContent(string $name, array $props, bool $withSlots): string
   {
      $content = "{# Composant {$name} #}\n";

      if (!empty($props)) {
         $content .= "{# Props disponibles:\n";
         foreach ($props as $prop) {
            $content .= "   - {$prop['name']} ({$prop['type']})" . ($prop['required'] ? ' - requis' : '') . "\n";
         }
         $content .= "#}\n\n";
      }

      if ($withSlots) {
         $content .= "{# Slots disponibles:\n";
         $content .= "   - Utilisez {{ slot_name }} pour afficher un slot\n";
         $content .= "#}\n\n";
      }

      $content .= "<div class=\"{$name}-component\">\n";
      $content .= "    {# Votre template ici #}\n";

      if (!empty($props)) {
         $content .= "\n    {# Exemples d'utilisation des props: #}\n";
         foreach ($props as $prop) {
            $content .= "    {# {{ {$prop['name']} }} #}\n";
         }
      }

      $content .= "</div>\n";

      return $content;
   }

   protected function getDefaultValueForType(string $type): string
   {
      return match ($type) {
         'string' => "''",
         'int', 'integer' => '0',
         'float', 'double' => '0.0',
         'bool', 'boolean' => 'false',
         'array' => '[]',
         'object' => 'null',
         default => 'null'
      };
   }
}
