<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration générale du CraftPanel
    |--------------------------------------------------------------------------
    |
    | Cette section définit les paramètres généraux du panneau d'administration.
    |
    */
    'name' => env('APP_NAME', 'IronFlow') . ' Admin',
    'version' => '1.0.0',
    'url_prefix' => 'admin',
    'middleware' => ['web', 'auth:craftpanel'],

    /*
    |--------------------------------------------------------------------------
    | Authentification
    |--------------------------------------------------------------------------
    |
    | Configuration de l'authentification pour le panneau d'administration.
    |
    */
    'auth' => [
        'guard' => 'craftpanel',
        'model' => \IronFlow\CraftPanel\Models\AdminUser::class,
        'password_timeout' => 10800, // 3 heures
        'login_throttle' => [
            'enabled' => true,
            'max_attempts' => 5,
            'decay_minutes' => 10,
        ],
        'two_factor' => [
            'enabled' => false,
            'provider' => 'sms', // 'sms', 'email', 'totp'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Interface utilisateur
    |--------------------------------------------------------------------------
    |
    | Personnalisation de l'interface utilisateur du panneau d'administration.
    |
    */
    'ui' => [
        'theme' => 'light', // 'light', 'dark', 'auto'
        'primary_color' => '#4f46e5', // Indigo
        'logo' => null, // Chemin vers le logo (null pour utiliser le texte)
        'favicon' => null, // Chemin vers le favicon
        'show_breadcrumbs' => true,
        'show_help_button' => true,
        'enable_animations' => true,
        'sidenav_condensed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalités
    |--------------------------------------------------------------------------
    |
    | Activation ou désactivation des fonctionnalités du panneau d'administration.
    |
    */
    'features' => [
        'activity_log' => true,
        'file_manager' => true,
        'backups' => true,
        'api_tokens' => true,
        'notifications' => true,
        'search' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Localisations
    |--------------------------------------------------------------------------
    |
    | Configuration des langues disponibles dans le panneau d'administration.
    |
    */
    'locales' => [
        'fr' => [
            'name' => 'Français',
            'flag' => '🇫🇷',
        ],
        'en' => [
            'name' => 'English',
            'flag' => '🇬🇧',
        ],
    ],
    'default_locale' => 'fr',

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Configuration des éléments de navigation supplémentaires.
    | Le menu principal est défini dans le CraftPanelController.
    |
    */
    'navigation' => [
        'external_links' => [
            [
                'title' => 'Documentation',
                'url' => '/docs',
                'icon' => 'book',
                'target' => '_blank',
            ],
            [
                'title' => 'Site web',
                'url' => '/',
                'icon' => 'globe',
                'target' => '_blank',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions par défaut
    |--------------------------------------------------------------------------
    |
    | Liste des permissions par défaut à créer lors de l'installation.
    |
    */
    'default_permissions' => [
        // Tableau de bord
        'dashboard.view' => [
            'name' => 'Voir le tableau de bord',
            'group' => 'Tableau de bord',
        ],

        // Utilisateurs
        'users.view' => [
            'name' => 'Voir les utilisateurs',
            'group' => 'Utilisateurs',
        ],
        'users.create' => [
            'name' => 'Créer des utilisateurs',
            'group' => 'Utilisateurs',
        ],
        'users.edit' => [
            'name' => 'Modifier des utilisateurs',
            'group' => 'Utilisateurs',
        ],
        'users.delete' => [
            'name' => 'Supprimer des utilisateurs',
            'group' => 'Utilisateurs',
        ],

        // Rôles et permissions
        'roles.view' => [
            'name' => 'Voir les rôles',
            'group' => 'Rôles et permissions',
        ],
        'roles.create' => [
            'name' => 'Créer des rôles',
            'group' => 'Rôles et permissions',
        ],
        'roles.edit' => [
            'name' => 'Modifier des rôles',
            'group' => 'Rôles et permissions',
        ],
        'roles.delete' => [
            'name' => 'Supprimer des rôles',
            'group' => 'Rôles et permissions',
        ],

        // Paramètres
        'settings.general' => [
            'name' => 'Gérer les paramètres généraux',
            'group' => 'Paramètres',
        ],
        'settings.appearance' => [
            'name' => 'Gérer l\'apparence',
            'group' => 'Paramètres',
        ],
        'settings.security' => [
            'name' => 'Gérer la sécurité',
            'group' => 'Paramètres',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rôles par défaut
    |--------------------------------------------------------------------------
    |
    | Liste des rôles par défaut à créer lors de l'installation.
    |
    */
    'default_roles' => [
        'super-admin' => [
            'name' => 'Super Administrateur',
            'description' => 'Accès complet à toutes les fonctionnalités',
            'permissions' => '*', // Toutes les permissions
        ],
        'admin' => [
            'name' => 'Administrateur',
            'description' => 'Accès à la plupart des fonctionnalités d\'administration',
            'permissions' => [
                'dashboard.view',
                'users.view',
                'users.create',
                'users.edit',
                'roles.view',
                'settings.general',
                'settings.appearance',
            ],
        ],
        'editor' => [
            'name' => 'Éditeur',
            'description' => 'Gestion du contenu uniquement',
            'permissions' => [
                'dashboard.view',
            ],
        ],
    ],
];
