<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Traits;

trait Administrable
{
    protected static array $adminConfig = [];
    protected static array $adminFields = [];
    protected static array $adminValidation = [];
    protected static array $adminRelations = [];
    protected static array $adminActions = [];

    public static function configureAdmin(array $config = []): void
    {
        static::$adminConfig = array_merge([
            'displayName' => class_basename(static::class),
            'pluralName' => strtolower(class_basename(static::class)) . 's',
            'perPage' => 10,
            'orderBy' => 'id',
            'orderDirection' => 'desc',
            'searchable' => [],
            'filterable' => [],
            'exportable' => true,
        ], $config);
    }

    public static function setAdminFields(array $fields): void
    {
        static::$adminFields = $fields;
    }

    public static function setAdminValidation(array $rules): void
    {
        static::$adminValidation = $rules;
    }

    public static function setAdminRelations(array $relations): void
    {
        static::$adminRelations = $relations;
    }

    public static function setAdminActions(array $actions): void
    {
        static::$adminActions = $actions;
    }

    public static function getAdminConfig(): array
    {
        return static::$adminConfig;
    }

    public static function getAdminFields(): array
    {
        return static::$adminFields;
    }

    public static function getAdminValidation(): array
    {
        return static::$adminValidation;
    }

    public static function getAdminRelations(): array
    {
        return static::$adminRelations;
    }

    public static function getAdminActions(): array
    {
        return static::$adminActions;
    }
}
