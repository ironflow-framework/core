<?php

namespace IronFlow\CraftPanel\Traits;


trait HasAdminModel
{
    /**
     * Champs du formulaire
     * @var array
     */
    protected static array $fields = [];

    /**
     * Règles de validation
     * @var array
     */
    protected static array $validationRules = [];

    /**
     * Relations à charger
     * @var array
     */
    protected static array $relations = [];

    /**
     * Filtres disponibles
     * @var array
     */
    protected static array $filters = [];

    /**
     * Actions disponibles
     * @var array
     */
    protected static array $actions = [];

    /**
     * Permissions du modèle
     * @var array
     */
    protected static array $permissions = [];

    /**
     * Nom d'affichage
     * @var string
     */
    protected static string $displayName = '';

    /**
     * Icône du modèle
     * @var string
     */
    protected static string $icon = '';

    /**
     * Champs à afficher dans la liste
     * @var array
     */
    protected static array $listFields = [];

    /**
     * Champs de recherche
     * @var array
     */
    protected static array $searchableFields = [];

    /**
     * Champs triables
     * @var array
     */
    protected static array $sortableFields = [];

    /**
     * Actions en masse
     * @var array
     */
    protected static array $bulkActions = [];

    /**
     * Widgets de statistiques
     * @var array
     */
    protected static array $dashboardWidgets = [];

    /**
     * Actions personnalisées
     * @var array
     */
    protected static array $customActions = [];

    /**
     * Options d'export
     * @var array
     */
    protected static array $exportOptions = [];

    /**
     * Récupère les champs du formulaire
     * @return array
     */
    public static function getFields(): array
    {
        return static::$fields;
    }

    /**
     * Récupère les règles de validation
     * @return array
     */
    public static function getValidationRules(): array
    {
        return static::$validationRules;
    }

    /**
     * Récupère les relations à charger
     * @return array
     */
    public static function getRelations(): array
    {
        return static::$relations;
    }

    /**
     * Récupère les filtres disponibles
     * @return array
     */
    public static function getFilters(): array
    {
        return static::$filters;
    }

    /**
     * Récupère les actions disponibles
     * @return array
     */
    public static function getActions(): array
    {
        return static::$actions;
    }

    /**
     * Récupère les permissions du modèle
     * @return array
     */
    public static function getPermissions(): array
    {
        return static::$permissions;
    }

    /**
     * Peut effectuer des actions
     * @var bool
     */
    protected static function can(string $action): bool
    {
        return static::$permissions[$action] ?? false;
    }

    /**
     * A une permission
     * @param string $permission
     * @return bool
     */
    protected static function hasPermission(string $permission): bool
    {
        return static::$permissions[$permission] ?? false;
    }

    /**
     * Récupère le nom d'affichage du modèle
     * @return string
     */
    public static function getDisplayName(): string
    {
        return static::$displayName;
    }

    /**
     * Récupère l'icône du modèle
     * @return string
     */
    public static function getIcon(): string
    {
        return static::$icon;
    }

    /**
     * Récupère les champs à afficher dans la liste
     * @return array
     */
    public static function getListFields(): array
    {
        return static::$listFields;
    }

    /**
     * Récupère les champs de recherche
     * @return array
     */
    public static function getSearchableFields(): array
    {
        return static::$searchableFields;
    }

    /**
     * Récupère les champs triables
     * @return array
     */
    public static function getSortableFields(): array
    {
        return static::$sortableFields;
    }

    /**
     * Récupère les actions en masse disponibles
     * @return array
     */
    public static function getBulkActions(): array
    {
        return static::$bulkActions;
    }

    /**
     * Récupère les widgets de statistiques
     * @return array
     */
    public static function getDashboardWidgets(): array
    {
        return static::$dashboardWidgets;
    }

    /**
     * Récupère les actions personnalisées
     * @return array
     */
    public static function getCustomActions(): array
    {
        return static::$customActions;
    }

    /**
     * Récupère les options d'export
     * @return array
     */
    public static function getExportOptions(): array
    {
        return static::$exportOptions;
    }
}
