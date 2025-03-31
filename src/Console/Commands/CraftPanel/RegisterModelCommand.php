<?php

namespace IronFlow\Console\Commands\CraftPanel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Filesystem;
use IronFlow\Support\Facades\Str;

class RegisterModelCommand extends Command
{
    protected static $defaultName = 'craftpanel:register-model';
    protected static $defaultDescription = 'Enregistrer un modèle dans le CraftPanel';

    protected function configure(): void
    {
        $this
            ->addArgument('model', InputArgument::REQUIRED, 'Nom du modèle à enregistrer')
            ->addOption('icon', null, InputOption::VALUE_OPTIONAL, 'Icône pour le modèle', 'database')
            ->addOption('display-name', null, InputOption::VALUE_OPTIONAL, 'Nom d\'affichage du modèle')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forcer la réinscription du modèle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $model = $input->getArgument('model');
        $icon = $input->getOption('icon');
        $displayName = $input->getOption('display-name') ?: Str::title(class_basename($model));
        $force = $input->getOption('force');

        try {
            // Vérifier si le modèle existe
            if (!class_exists($model)) {
                $io->error("Le modèle {$model} n'existe pas.");
                return Command::FAILURE;
            }

            // Vérifier si le modèle est déjà enregistré
            if (!$force && $this->isModelRegistered($model)) {
                $io->error("Le modèle {$model} est déjà enregistré. Utilisez --force pour réenregistrer.");
                return Command::FAILURE;
            }

            // Générer les permissions
            $permissions = $this->generatePermissions($model);

            // Mettre à jour la configuration
            $this->updateConfig($model, $icon, $displayName, $permissions);

            // Créer les vues spécifiques au modèle
            $this->createModelViews($model, $io);

            $io->success("Le modèle {$model} a été enregistré avec succès !");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'enregistrement du modèle : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function isModelRegistered(string $model): bool
    {
        $config = require base_path('config/craftpanel.php');
        return isset($config['models'][$model]);
    }

    protected function generatePermissions(string $model): array
    {
        $shortName = class_basename($model);
        return [
            'view' => "view-{$shortName}",
            'create' => "create-{$shortName}",
            'edit' => "edit-{$shortName}",
            'delete' => "delete-{$shortName}",
        ];
    }

    protected function updateConfig(string $model, string $icon, string $displayName, array $permissions): void
    {
        $configPath = base_path('config/craftpanel.php');

        // Charger la configuration existante
        $config = file_exists($configPath)
            ? require $configPath
            : ['models' => []];

        // Mettre à jour la configuration du modèle
        $config['models'][$model] = [
            'icon' => $icon,
            'displayName' => $displayName,
            'permissions' => $permissions,
        ];

        // Formater et écrire la configuration
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";";
        Filesystem::put($configPath, $configContent);
    }

    protected function createModelViews(string $model, SymfonyStyle $io): void
    {
        $shortName = class_basename($model);
        $viewBasePath = resource_path("views/craftpanel/{$shortName}");

        // Créer le dossier des vues si nécessaire
        if (!Filesystem::exists($viewBasePath)) {
            Filesystem::makeDirectory($viewBasePath, 0755, true);
        }

        $views = [
            'index.twig' => $this->getViewTemplate('index'),
            'create.twig' => $this->getViewTemplate('create'),
            'edit.twig' => $this->getViewTemplate('edit'),
        ];

        foreach ($views as $filename => $content) {
            $viewPath = "{$viewBasePath}/{$filename}";

            // Ne pas écraser les fichiers existants sauf si --force est utilisé
            if (!file_exists($viewPath)) {
                Filesystem::put($viewPath, $content);
                $io->note("Vue créée : {$viewPath}");
            }
        }
    }

    protected function getViewTemplate(string $type): string
    {
        return match ($type) {
            'index' => $this->getIndexViewTemplate(),
            'create' => $this->getCreateViewTemplate(),
            'edit' => $this->getEditViewTemplate(),
        };
    }

    protected function getIndexViewTemplate(): string
    {
        return <<<'TWIG'
{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">{{ title }}</h1>
            <a href="{{ route('craftpanel.create', {model: model}) }}" class="btn btn-primary">
                <i class="ti ti-plus mr-2"></i>
                {{ __('Ajouter') }}
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            {% for field in fields %}
                                <th>{{ field.label }}</th>
                            {% endfor %}
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for item in items %}
                            <tr>
                                {% for field in fields %}
                                    <td>{{ item[field.name] }}</td>
                                {% endfor %}
                                <td>
                                    <a href="{{ route('craftpanel.edit', {model: model, id: item.id}) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('craftpanel.destroy', {model: model, id: item.id}) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error" onclick="return confirm('Êtes-vous sûr ?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{% endblock %}
TWIG;
    }

    protected function getCreateViewTemplate(): string
    {
        return <<<'TWIG'
{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">{{ title }}</h1>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('craftpanel.store', {model: model}) }}">
                    @csrf

                    {% for field in fields %}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">{{ field.label }}</span>
                            </label>
                            <input type="{{ field.type }}" name="{{ field.name }}" class="input input-bordered" required>
                        </div>
                    {% endfor %}

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check mr-2"></i>
                            {{ __('Enregistrer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{% endblock %}
TWIG;
    }

    protected function getEditViewTemplate(): string
    {
        return <<<'TWIG'
{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">{{ title }}</h1>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('craftpanel.update', {model: model, id: item.id}) }}">
                    @csrf
                    @method('PUT')

                    {% for field in fields %}
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">{{ field.label }}</span>
                            </label>
                            <input type="{{ field.type }}" name="{{ field.name }}" class="input input-bordered" value="{{ item[field.name] }}" required>
                        </div>
                    {% endfor %}

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check mr-2"></i>
                            {{ __('Mettre à jour') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{% endblock %}
TWIG;
    }
}
