<?php

namespace IronFlow\CraftPanel\Contracts;

interface AdminModel
{
    /**
     * Récupère les champs du formulaire
     * @return array
     */
    public static function getFields(): array;

    /**
     * Récupère les règles de validation
     * @return array
     */
    public static function getValidationRules(): array;

    /**
     * Récupère les relations à charger
     * @return array
     */
    public static function getRelations(): array;

    /**
     * Récupère les filtres disponibles
     * @return array
     */
    public static function getFilters(): array;

    /**
     * Récupère les actions disponibles
     * @return array
     */
    public static function getActions(): array;

    /**
     * Récupère les permissions du modèle
     * @return array
     */
    public static function getPermissions(): array;

    /**
     * Récupère le nom d'affichage du modèle
     * @return string
     */
    public static function getDisplayName(): string;

    /**
     * Récupère l'icône du modèle
     * @return string
     */
    public static function getIcon(): string;

    /**
     * Récupère les champs à afficher dans la liste
     * @return array
     */
    public static function getListFields(): array;

    /**
     * Récupère les champs de recherche
     * @return array
     */
    public static function getSearchableFields(): array;

    /**
     * Récupère les champs triables
     * @return array
     */
    public static function getSortableFields(): array;

    /**
     * Récupère les actions en masse disponibles
     * @return array
     */
    public static function getBulkActions(): array;

    /**
     * Récupère les widgets de statistiques
     * @return array
     */
    public static function getDashboardWidgets(): array;

    /**
     * Récupère les actions personnalisées
     * @return array
     */
    public static function getCustomActions(): array;

    /**
     * Récupère les options d'export
     * @return array
     */
    public static function getExportOptions(): array;
}
