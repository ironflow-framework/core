<?php

namespace IronFlow\Console\Commands\CraftPanel;

use IronFlow\Console\Commands\Command;
use IronFlow\Support\Filesystem;
use IronFlow\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Config\Repository as Config;

class RegisterModelCommand extends Command
{
    protected $signature = 'craftpanel:register {model : Nom du modèle à enregistrer} {--icon=database : Icône pour le modèle} {--display-name= : Nom d\'affichage du modèle}';

    protected $description = 'Enregistrer un modèle dans le CraftPanel';

    public function handle(): int
    {
        $model = $this->argument('model');
        $icon = $this->option('icon') ?: 'database';
        $displayName = $this->option('display-name') ?: Str::title($model);

        $this->info("Enregistrement du modèle {$model} dans le CraftPanel...");

        // Vérifier si le modèle existe
        if (!class_exists($model)) {
            $this->error("Le modèle {$model} n'existe pas.");
            return 1;
        }

        // Générer les permissions
        $permissions = $this->generatePermissions($model);

        // Mettre à jour la configuration
        $this->updateConfig($model, $icon, $displayName, $permissions);

        // Créer les vues spécifiques au modèle
        $this->createModelViews($model);

        $this->info("Le modèle {$model} a été enregistré avec succès !");
        return 0;
    }

    private function generatePermissions(string $model): array
    {
        return [
            'view' => "view-{$model}",
            'create' => "create-{$model}",
            'edit' => "edit-{$model}",
            'delete' => "delete-{$model}",
        ];
    }

    private function updateConfig(string $model, string $icon, string $displayName, array $permissions): void
    {
        $config = Config::get('craftpanel.models', []);
        $config[$model] = [
            'icon' => $icon,
            'displayName' => $displayName,
            'permissions' => $permissions,
        ];

        Filesystem::put(
            base_path('config/craftpanel.php'),
            var_export(['models' => $config], true)
        );
    }

    private function createModelViews(string $model): void
    {
        $views = [
            'index' => 'index.twig',
            'create' => 'create.twig',
            'edit' => 'edit.twig',
        ];

        foreach ($views as $name => $template) {
            $viewPath = resource_path("views/craftpanel/{$model}/{$name}.twig");
            
            if (!file_exists($viewPath)) {
                Filesystem::put($viewPath, $this->getViewTemplate($template));
            }
        }
    }

    private function getViewTemplate(string $template): string
    {
        return match ($template) {
            'index.twig' => "{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class=\"p-4\">
        <div class=\"flex justify-between items-center mb-4\">
            <h1 class=\"text-2xl font-bold\">{{ title }}</h1>
            <a href=\"{{ route('craftpanel.create', {model: model}) }}\" class=\"btn btn-primary\">
                <i class=\"ti ti-plus mr-2\"></i>
                {{ __('Ajouter') }}
            </a>
        </div>

        <div class=\"card\">
            <div class=\"card-body\">
                <table class=\"table\">
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
                                    <a href=\"{{ route('craftpanel.edit', {model: model, id: item.id}) }}\" class=\"btn btn-sm btn-primary\">
                                        <i class=\"ti ti-edit\"></i>
                                    </a>
                                    <form method=\"POST\" action=\"{{ route('craftpanel.destroy', {model: model, id: item.id}) }}\" class=\"inline\">
                                        @csrf
                                        @method('DELETE')
                                        <button type=\"submit\" class=\"btn btn-sm btn-error\" onclick=\"return confirm('Êtes-vous sûr ?')\">
                                            <i class=\"ti ti-trash\"></i>
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
{% endblock %}",
            'create.twig' => "{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class=\"p-4\">
        <h1 class=\"text-2xl font-bold mb-4\">{{ title }}</h1>

        <div class=\"card\">
            <div class=\"card-body\">
                <form method=\"POST\" action=\"{{ route('craftpanel.store', {model: model}) }}\">
                    @csrf

                    {% for field in fields %}
                        <div class=\"form-control mb-4\">
                            <label class=\"label\">
                                <span class=\"label-text\">{{ field.label }}</span>
                            </label>
                            <input type=\"{{ field.type }}\" name=\"{{ field.name }}\" class=\"input input-bordered\" required>
                        </div>
                    {% endfor %}

                    <div class=\"flex justify-end\">
                        <button type=\"submit\" class=\"btn btn-primary\">
                            <i class=\"ti ti-check mr-2\"></i>
                            {{ __('Enregistrer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{% endblock %}",
            'edit.twig' => "{% extends 'CraftPanel::layouts.app' %}

{% block content %}
    <div class=\"p-4\">
        <h1 class=\"text-2xl font-bold mb-4\">{{ title }}</h1>

        <div class=\"card\">
            <div class=\"card-body\">
                <form method=\"POST\" action=\"{{ route('craftpanel.update', {model: model, id: item.id}) }}\">
                    @csrf
                    @method('PUT')

                    {% for field in fields %}
                        <div class=\"form-control mb-4\">
                            <label class=\"label\">
                                <span class=\"label-text\">{{ field.label }}</span>
                            </label>
                            <input type=\"{{ field.type }}\" name=\"{{ field.name }}\" class=\"input input-bordered\" value=\"{{ item[field.name] }}\" required>
                        </div>
                    {% endfor %}

                    <div class=\"flex justify-end\">
                        <button type=\"submit\" class=\"btn btn-primary\">
                            <i class=\"ti ti-check mr-2\"></i>
                            {{ __('Mettre à jour') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{% endblock %}",
        };
    }
}
