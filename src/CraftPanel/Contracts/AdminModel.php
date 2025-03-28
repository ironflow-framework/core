<?php

namespace IronFlow\CraftPanel\Contracts;

interface AdminModel
{
    /**
     * Récupère les champs du formulaire
     * @return array
     */
    public static function getFormFields(): array;

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
}
