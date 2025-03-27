<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Database\Model;
use IronFlow\CraftPanel\Traits\Administrable;
use ReflectionClass;

class CraftPanelController extends Controller
{
    protected string $modelClass;
    protected Model $model;

    public function __construct(string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} not found");
        }

        $reflection = new ReflectionClass($modelClass);
        if (!$reflection->hasMethod('configureAdmin')) {
            throw new \InvalidArgumentException("Model {$modelClass} must use the Administrable trait");
        }

        $this->modelClass = $modelClass;
        $this->model = new $modelClass();
    }

    public function index(Request $request): Response
    {
        $config = $this->modelClass::getAdminConfig();
        $query = $this->modelClass::query();

        // Appliquer la recherche
        if ($search = $request->get('search')) {
            $searchable = $config['searchable'] ?? [];
            foreach ($searchable as $field) {
                $query->orWhere($field, 'LIKE', "%{$search}%");
            }
        }

        // Appliquer les filtres
        $filters = $request->get('filters', []);
        foreach ($filters as $field => $value) {
            if (in_array($field, $config['filterable'] ?? [])) {
                $query->where($field, $value);
            }
        }

        // Appliquer le tri
        $orderBy = $request->get('orderBy', $config['orderBy'] ?? 'id');
        $orderDirection = $request->get('orderDirection', $config['orderDirection'] ?? 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $request->get('perPage', $config['perPage'] ?? 10);
        $items = $query->paginate($perPage);

        return $this->view('craftpanel::index', [
            'items' => $items,
            'config' => $config,
            'fields' => $this->modelClass::getAdminFields(),
            'actions' => $this->modelClass::getAdminActions(),
        ]);
    }

    public function create(): Response
    {
        return $this->view('craftpanel::create', [
            'config' => $this->modelClass::getAdminConfig(),
            'fields' => $this->modelClass::getAdminFields(),
            'validation' => $this->modelClass::getAdminValidation(),
        ]);
    }

    public function store(Request $request): Response
    {
        $validation = $this->modelClass::getAdminValidation();
        $validated = $this->validate($request->all(), $validation);

        $item = $this->modelClass::create($validated);

        // Log l'action
        $this->logActivity('create', $item);

        return $this->redirect()
            ->route('craftpanel.index', ['model' => class_basename($this->modelClass)])
            ->with('success', 'Item created successfully');
    }

    public function edit(int $id): Response
    {
        $item = $this->modelClass::findOrFail($id);

        return $this->view('craftpanel::edit', [
            'item' => $item,
            'config' => $this->modelClass::getAdminConfig(),
            'fields' => $this->modelClass::getAdminFields(),
            'validation' => $this->modelClass::getAdminValidation(),
            'relations' => $this->modelClass::getAdminRelations(),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $item = $this->modelClass::findOrFail($id);
        $validation = $this->modelClass::getAdminValidation();
        $validated = $request->validate($validation);

        $oldValues = $item->getAttributes();
        $item->update($validated);

        // Log l'action avec les changements
        $this->logActivity('update', $item, [
            'old' => $oldValues,
            'new' => $item->getAttributes(),
        ]);

        return $this->redirect()
            ->route('craftpanel.index', ['model' => class_basename($this->modelClass)])
            ->with('success', 'Item updated successfully');
    }

    public function destroy(int $id): Response
    {
        $item = $this->modelClass::findOrFail($id);
        $item->delete();

        // Log l'action
        $this->logActivity('delete', $item);

        return $this->redirect()
            ->route('craftpanel.index', ['model' => class_basename($this->modelClass)])
            ->with('success', 'Item deleted successfully');
    }

    public function export(Request $request): Response
    {
        $config = $this->modelClass::getAdminConfig();
        if (!($config['exportable'] ?? false)) {
            abort(403, 'Export not allowed for this model');
        }

        $fields = $this->modelClass::getAdminFields();
        $items = $this->modelClass::all();

        $csv = [];
        // En-têtes
        $csv[] = array_map(fn($field) => $field['label'], $fields);

        // Données
        foreach ($items as $item) {
            $row = [];
            foreach ($fields as $key => $field) {
                $row[] = $item->$key;
            }
            $csv[] = $row;
        }

        $filename = strtolower(class_basename($this->modelClass)) . '_' . date('Y-m-d_His') . '.csv';
        
        return $this->response()
            ->streamDownload(function () use ($csv) {
                $handle = fopen('php://output', 'w');
                foreach ($csv as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
    }

    protected function logActivity(string $action, Model $model, array $changes = []): void
    {
        $user = auth()->user();
        if (!$user) return;

        \IronFlow\CraftPanel\Models\AdminActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'changes' => $changes ? json_encode($changes) : null,
        ]);
    }
}
