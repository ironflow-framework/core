<?php

namespace IronFlow\CraftPanel\Controllers;

use IronFlow\Http\Controllers\Controller;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Request;
use IronFlow\Support\Facades\Response;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Validator;
use IronFlow\Support\Facades\Flash;
use IronFlow\Support\Facades\Redirect;
use IronFlow\Support\Facades\Str;
use IronFlow\Support\Facades\Log;
use IronFlow\CraftPanel\Contracts\AdminModel;

class CraftPanelController extends Controller
{
    /**
     * Modèle actuellement géré
     * @var string|null
     */
    protected ?string $model = null;
    
    /**
     * Configuration du modèle
     * @var array
     */
    protected array $config = [];

    /**
     * Affiche le tableau de bord
     * @return Response
     */
    public function dashboard(): Response
    {
        $stats = $this->getDashboardStats();
        $models = $this->getAdminModels();
        
        return View::make('CraftPanel::dashboard', [
            'stats' => $stats,
            'models' => $models,
            'title' => Config::get('craftpanel.title'),
        ]);
    }

    /**
     * Affiche la liste des éléments d'un modèle
     * @param string $model Nom du modèle
     * @return Response
     */
    public function index(string $model): Response
    {
        $this->checkModelPermission($model, 'view');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $items = $this->getItems($modelClass);
        $fields = $modelClass::getFormFields();
        $filters = $modelClass::getFilters();
        
        return View::make('CraftPanel::index', [
            'model' => $model,
            'modelClass' => $modelClass,
            'items' => $items,
            'fields' => $fields,
            'filters' => $filters,
            'title' => $modelClass::getDisplayName(),
        ]);
    }

    /**
     * Affiche le formulaire de création
     * @param string $model Nom du modèle
     * @return Response
     */
    public function create(string $model): Response
    {
        $this->checkModelPermission($model, 'create');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $fields = $modelClass::getFormFields();
        
        return View::make('CraftPanel::create', [
            'model' => $model,
            'modelClass' => $modelClass,
            'fields' => $fields,
            'title' => "Créer " . $modelClass::getDisplayName(),
        ]);
    }

    /**
     * Enregistre un nouvel élément
     * @param string $model Nom du modèle
     * @return Response
     */
    public function store(string $model): Response
    {
        $this->checkModelPermission($model, 'create');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $validator = Validator::make(Request::all(), $modelClass::getValidationRules());

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $item = $modelClass::create(Request::all());
        Log::info("Nouvel élément créé dans le modèle {$model}", ['id' => $item->id]);
        Flash::success('L\'élément a été créé avec succès.');

        return Redirect::route('craftpanel.index', ['model' => $model]);
    }

    /**
     * Affiche le formulaire d'édition
     * @param string $model Nom du modèle
     * @param int $id Identifiant de l'élément
     * @return Response
     */
    public function edit(string $model, int $id): Response
    {
        $this->checkModelPermission($model, 'edit');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $item = $this->getItem($modelClass, $id);
        $fields = $modelClass::getFormFields();
        
        return View::make('CraftPanel::edit', [
            'model' => $model,
            'modelClass' => $modelClass,
            'item' => $item,
            'fields' => $fields,
            'title' => "Modifier " . $modelClass::getDisplayName(),
        ]);
    }

    /**
     * Met à jour un élément
     * @param string $model Nom du modèle
     * @param int $id Identifiant de l'élément
     * @return Response
     */
    public function update(string $model, int $id): Response
    {
        $this->checkModelPermission($model, 'edit');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $item = $this->getItem($modelClass, $id);
        $validator = Validator::make(Request::all(), $modelClass::getValidationRules());

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $oldData = $item->toArray();
        $item->update(Request::all());
        Log::info("Élément mis à jour dans le modèle {$model}", [
            'id' => $id,
            'changes' => array_diff_assoc($item->toArray(), $oldData)
        ]);
        Flash::success('L\'élément a été mis à jour avec succès.');

        return Redirect::route('craftpanel.index', ['model' => $model]);
    }

    /**
     * Supprime un élément
     * @param string $model Nom du modèle
     * @param int $id Identifiant de l'élément
     * @return Response
     */
    public function destroy(string $model, int $id): Response
    {
        $this->checkModelPermission($model, 'delete');
        $this->checkModelExists($model);

        $modelClass = $this->getModelClass($model);
        $item = $this->getItem($modelClass, $id);
        $item->delete();
        Log::info("Élément supprimé du modèle {$model}", ['id' => $id]);
        Flash::success('L\'élément a été supprimé avec succès.');

        return Redirect::route('craftpanel.index', ['model' => $model]);
    }

    /**
     * Affiche les paramètres
     * @return Response
     */
    public function settings(): Response
    {
        return View::make('CraftPanel::settings', [
            'title' => 'Paramètres',
        ]);
    }

    /**
     * Met à jour les paramètres
     * @return Response
     */
    public function updateSettings(): Response
    {
        // TODO: Implémenter la mise à jour des paramètres
        return Redirect::route('craftpanel.settings');
    }

    /**
     * Vérifie les permissions du modèle
     * @param string $model Nom du modèle
     * @param string $action Action à vérifier
     * @return void
     */
    private function checkModelPermission(string $model, string $action): void
    {
        $modelClass = $this->getModelClass($model);
        $permission = $modelClass::getPermissions()[$action] ?? null;
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403);
        }
    }

    /**
     * Vérifie l'existence du modèle
     * @param string $model Nom du modèle
     * @return void
     */
    private function checkModelExists(string $model): void
    {
        if (!class_exists($model) || !in_array(AdminModel::class, class_implements($model))) {
            abort(404, "Le modèle {$model} n'est pas un modèle administrable");
        }
    }

    /**
     * Récupère les statistiques du tableau de bord
     * @return array
     */
    private function getDashboardStats(): array
    {
        $stats = [];
        $models = $this->getAdminModels();
        
        foreach ($models as $model) {
            $stats[$model::class] = [
                'count' => $model::count(),
                'displayName' => $model::getDisplayName(),
                'icon' => $model::getIcon(),
            ];
        }
        
        return $stats;
    }

    /**
     * Récupère les modèles administrables
     * @return array
     */
    private function getAdminModels(): array
    {
        return array_filter(get_declared_classes(), function ($class) {
            return in_array(AdminModel::class, class_implements($class));
        });
    }

    /**
     * Récupère les éléments avec leurs relations
     * @param string $modelClass Classe du modèle
     * @return mixed
     */
    private function getItems($modelClass)
    {
        return $modelClass::with($modelClass::getRelations())
            ->paginate(Config::get('craftpanel.pagination.items_per_page', 15));
    }

    /**
     * Récupère un élément avec ses relations
     * @param string $modelClass Classe du modèle
     * @param int $id Identifiant de l'élément
     * @return mixed
     */
    private function getItem($modelClass, int $id)
    {
        return $modelClass::with($modelClass::getRelations())
            ->findOrFail($id);
    }

    /**
     * Récupère la classe du modèle
     * @param string $model Nom du modèle
     * @return string
     */
    private function getModelClass(string $model): string
    {
        return Str::studly($model);
    }
}
