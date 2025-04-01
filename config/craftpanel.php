<?php

return [
    // Configuration générale
    'general' => [
        'title' => env('CRAFTPANEL_TITLE', 'CraftPanel'),
        'logo' => env('CRAFTPANEL_LOGO', 'img/logo.svg'),
        'version' => env('CRAFTPANEL_VERSION', '1.0.0'),
    ],

    // Configuration des routes
    'routes' => [
        'prefix' => env('CRAFTPANEL_PREFIX', 'craftpanel'),
        'middleware' => [
            'web',
            'auth',
            'auth.craftpanel',
        ],
        'name' => env('CRAFTPANEL_NAMESPACE', 'craftpanel'),
    ],

    // Configuration du thème
    'theme' => [
        'default' => env('CRAFTPANEL_THEME', 'light'),
        'options' => ['light', 'dark'],
        'custom_css' => env('CRAFTPANEL_CUSTOM_CSS', 'css/custom.css'),
    ],

    // Configuration des permissions
    'permissions' => [
        'base' => [
            'view' => 'view-craftpanel',
            'manage' => 'manage-craftpanel',
        ],
        'models' => [
            // Les permissions des modèles seront définies directement dans les modèles
        ],
    ],

    // Configuration de la pagination
    'pagination' => [
        'items_per_page' => 15,
        'max_items_per_page' => 100,
    ],

    // Configuration des vues
    'views' => [
        'layout' => 'CraftPanel::layouts.app',
        'components' => [
            'navbar' => 'CraftPanel::components.navbar',
            'sidebar' => 'CraftPanel::components.sidebar',
            'header' => 'CraftPanel::components.header',
            'footer' => 'CraftPanel::components.footer',
        ],
    ],

    // Configuration des composants
    'components' => [
        'form' => [
            'default' => 'CraftPanel::components.form',
            'fields' => [
                'text' => 'CraftPanel::components.form.text',
                'textarea' => 'CraftPanel::components.form.textarea',
                'select' => 'CraftPanel::components.form.select',
                'checkbox' => 'CraftPanel::components.form.checkbox',
                'radio' => 'CraftPanel::components.form.radio',
                'file' => 'CraftPanel::components.form.file',
            ],
        ],
        'table' => [
            'default' => 'CraftPanel::components.table',
            'columns' => [
                'text' => 'CraftPanel::components.table.columns.text',
                'number' => 'CraftPanel::components.table.columns.number',
                'date' => 'CraftPanel::components.table.columns.date',
                'actions' => 'CraftPanel::components.table.columns.actions',
            ],
        ],
        'card' => [
            'default' => 'CraftPanel::components.card',
            'types' => [
                'default' => 'CraftPanel::components.card.default',
                'stat' => 'CraftPanel::components.card.stat',
            ],
        ],
    ],

    // Configuration des assets
    'assets' => [
        'css' => [
            'tailwind' => 'css/craftpanel.css',
            'custom' => env('CRAFTPANEL_CUSTOM_CSS', 'css/custom.css'),
        ],
        'js' => [
            'alpine' => 'js/alpine.js',
            'custom' => 'js/custom.js',
        ],
    ],

    // Configuration des filtres
    'filters' => [
        'default' => [
            'search' => true,
            'date_range' => true,
            'status' => true,
        ],
    ],

    // Configuration des actions
    'actions' => [
        'default' => [
            'create' => true,
            'edit' => true,
            'delete' => true,
            'export' => true,
        ],
    ],
];
