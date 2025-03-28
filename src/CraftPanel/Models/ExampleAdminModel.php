<?php

namespace IronFlow\CraftPanel\Models;

use IronFlow\CraftPanel\Contracts\AdminModel;
use IronFlow\Support\Facades\Validator;
use IronFlow\CraftPanel\Traits\HasAdminModel;

class ExampleAdminModel implements AdminModel
{
    use HasAdminModel;

    protected $table = 'examples';

    protected static array $fields = [
        'name' => [
            'type' => 'text',
            'label' => 'Nom',
            'required' => true,
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'required' => false,
        ],
        'status' => [
            'type' => 'select',
            'label' => 'Statut',
            'required' => true,
            'options' => [
                'active' => 'Actif',
                'inactive' => 'Inactif',
            ],
        ],
    ];

    protected static array $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive',
    ];

    protected static array $relations = [];

    protected static array $filters = [
        'search' => [
            'label' => 'Rechercher',
            'fields' => ['name', 'description'],
        ],
        'status' => [
            'label' => 'Statut',
            'type' => 'select',
            'options' => [
                'active' => 'Actif',
                'inactive' => 'Inactif',
            ],
        ],
    ];

    protected static array $actions = [
        'create' => true,
        'edit' => true,
        'delete' => true,
        'export' => true,
    ];

    protected static array $permissions = [
        'view' => 'view-examples',
        'create' => 'create-examples',
        'edit' => 'edit-examples',
        'delete' => 'delete-examples',
    ];

    protected static string $displayName = 'Exemples';

    protected static string $icon = 'ti ti-example';

    protected static array $listFields = [
        'name' => ['label' => 'Nom', 'sortable' => true],
        'status' => ['label' => 'Statut', 'sortable' => true],
        'created_at' => ['label' => 'Créé le', 'sortable' => true],
    ];

    protected static array $searchableFields = ['name', 'description'];

    protected static array $sortableFields = ['name', 'status', 'created_at'];

    protected static array $bulkActions = [
        'delete' => [
            'label' => 'Supprimer',
            'confirm' => 'Êtes-vous sûr de vouloir supprimer ces éléments ?',
        ],
        'activate' => [
            'label' => 'Activer',
            'confirm' => 'Êtes-vous sûr de vouloir activer ces éléments ?',
        ],
        'deactivate' => [
            'label' => 'Désactiver',
            'confirm' => 'Êtes-vous sûr de vouloir désactiver ces éléments ?',
        ],
    ];

    protected static array $dashboardWidgets = [
        'total' => [
            'label' => 'Total des exemples',
            'query' => 'count',
        ],
        'active' => [
            'label' => 'Exemples actifs',
            'query' => 'count',
            'where' => ['status' => 'active'],
        ],
        'inactive' => [
            'label' => 'Exemples inactifs',
            'query' => 'count',
            'where' => ['status' => 'inactive'],
        ],
    ];

    protected static array $customActions = [
        'duplicate' => [
            'label' => 'Dupliquer',
            'icon' => 'ti ti-copy',
            'action' => 'duplicate',
        ],
    ];

    protected static array $exportOptions = [
        'csv' => [
            'label' => 'Exporter en CSV',
            'fields' => ['name', 'status', 'created_at'],
        ],
        'excel' => [
            'label' => 'Exporter en Excel',
            'fields' => ['name', 'status', 'created_at'],
        ],
    ];
}
