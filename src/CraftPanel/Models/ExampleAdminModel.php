<?php

namespace IronFlow\CraftPanel\Models;

use IronFlow\CraftPanel\Contracts\AdminModel;
use IronFlow\Support\Facades\Validator;

class ExampleAdminModel implements AdminModel
{
    protected $table = 'examples';

    public static function getFormFields(): array
    {
        return [
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
    }

    public static function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getFilters(): array
    {
        return [
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
    }

    public static function getActions(): array
    {
        return [
            'create' => true,
            'edit' => true,
            'delete' => true,
            'export' => true,
        ];
    }

    public static function getPermissions(): array
    {
        return [
            'view' => 'view-examples',
            'create' => 'create-examples',
            'edit' => 'edit-examples',
            'delete' => 'delete-examples',
        ];
    }

    public static function getDisplayName(): string
    {
        return 'Exemples';
    }

    public static function getIcon(): string
    {
        return 'ti ti-example';
    }
}
