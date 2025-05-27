<?php

declare(strict_types=1);

namespace IronFlow\Components;

use IronFlow\View\TwigView;
use IronFlow\Components\Exceptions\ComponentException;

abstract class BaseComponent
{
    protected array $props = [];
    protected array $slots = [];
    protected TwigView $view;
    protected array $validationRules = [];
    protected array $defaultProps = [];

    /**
     * Constructor.
     *
     * @param mixed ...$args
     */
    public function __construct(...$args)
    {
        $this->props = $this->processArguments($args);
        $this->view = TwigView::getInstance();
        $this->validateProps();
    }

    abstract public function render(): string;

    /**
     * Définit les règles de validation pour les props
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Définit les props par défaut
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Rendu du template avec fusion des données
     */
    protected function view(string $template, array $data = []): string
    {
        $templateData = array_merge(
            $this->defaultProps,
            $this->props,
            $this->slots,
            $data
        );

        dump($templateData); // Pour le débogage, à retirer en production

        return $this->view->render("templates/{$template}", $templateData);
    }

    /**
     * Traite les arguments passés au constructeur de manière flexible
     */
    protected function processArguments(array $args): array
    {
        if (empty($args)) {
            return $this->defaults();
        }

        $processedProps = $this->defaults();

        // Si un seul argument qui est un tableau
        if (count($args) === 1 && is_array($args[0])) {
            return $this->processSingleArrayArgument($args[0]);
        }

        // Traitement des arguments multiples
        return $this->processMultipleArguments($args);
    }

    /**
     * Traite le cas d'un seul argument qui est un tableau
     */    protected function processSingleArrayArgument(array $arg): array
    {
        $processedProps = $this->defaults();
        $defaultKeys = array_keys($processedProps);

        // Si c'est un tableau associatif
        if ($this->isAssociativeArray($arg)) {
            return array_merge($processedProps, $arg);
        }

        // Si c'est un tableau indexé, on mappe les valeurs aux clés par défaut
        foreach ($arg as $index => $value) {
            if (is_array($value) && $this->isAssociativeArray($value)) {
                $processedProps = array_merge($processedProps, $value);
            } else if (isset($defaultKeys[$index])) {
                // Map la valeur indexée à la clé nommée correspondante
                $processedProps[$defaultKeys[$index]] = $value;
            }
        }

        return $processedProps;
    }

    /**
     * Traite le cas de plusieurs arguments
     */    protected function processMultipleArguments(array $args): array
    {
        $processedProps = $this->defaults();
        $defaultKeys = array_keys($processedProps);
        $namedProps = [];

        foreach ($args as $index => $value) {
            if (is_array($value)) {
                if ($this->isAssociativeArray($value)) {
                    // Tableau associatif - on fusionne directement les clés
                    $namedProps = array_merge($namedProps, $value);
                } else {
                    // Tableau indexé - on mappe chaque valeur à la clé correspondante
                    foreach ($value as $subIndex => $subValue) {
                        if (is_array($subValue) && $this->isAssociativeArray($subValue)) {
                            $namedProps = array_merge($namedProps, $subValue);
                        } else if (isset($defaultKeys[$subIndex])) {
                            $processedProps[$defaultKeys[$subIndex]] = $subValue;
                        }
                    }
                }
            } else if (isset($defaultKeys[$index])) {
                // Valeur simple - on mappe à la clé nommée correspondante
                $processedProps[$defaultKeys[$index]] = $value;
            }
        }

        // On fusionne dans l'ordre : defaults puis nommés
        return array_merge($processedProps, $namedProps);
    }

    /**
     * Valide les props selon les règles définies
     */
    protected function validateProps(): void
    {
        $rules = $this->rules();
        if (empty($rules)) {
            return;
        }

        foreach ($rules as $prop => $rule) {
            $this->validateProp($prop, $rule);
        }
    }

    /**
     * Valide une prop spécifique
     */
    protected function validateProp(string $prop, $rule): void
    {
        $value = $this->props[$prop] ?? null;

        if (is_string($rule)) {
            $this->validateByType($prop, $value, $rule);
        } elseif (is_array($rule)) {
            $this->validateByRules($prop, $value, $rule);
        } elseif (is_callable($rule)) {
            $this->validateByCallback($prop, $value, $rule);
        }
    }

    /**
     * Validation par type
     */
    protected function validateByType(string $prop, $value, string $type): void
    {
        $isValid = match ($type) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'callable' => is_callable($value),
            'null' => is_null($value),
            'numeric' => is_numeric($value),
            default => true
        };

        if (!$isValid) {
            throw new ComponentException(
                "La prop '{$prop}' doit être de type '{$type}', " . gettype($value) . " fourni."
            );
        }
    }

    /**
     * Validation par règles complexes
     */
    protected function validateByRules(string $prop, $value, array $rules): void
    {
        foreach ($rules as $rule => $constraint) {
            match ($rule) {
                'required' => $constraint && $this->validateRequired($prop, $value),
                'type' => $this->validateByType($prop, $value, $constraint),
                'min' => $this->validateMin($prop, $value, $constraint),
                'max' => $this->validateMax($prop, $value, $constraint),
                'in' => $this->validateIn($prop, $value, $constraint),
                'instanceof' => $this->validateInstanceOf($prop, $value, $constraint),
                default => null
            };
        }
    }

    /**
     * Validation par callback
     */
    protected function validateByCallback(string $prop, $value, callable $callback): void
    {
        if (!$callback($value)) {
            throw new ComponentException("La prop '{$prop}' n'a pas passé la validation personnalisée.");
        }
    }

    /**
     * Validation required
     */
    protected function validateRequired(string $prop, $value): void
    {
        if (!isset($this->props[$prop]) || $value === null || $value === '') {
            throw new ComponentException("La prop '{$prop}' est requise.");
        }
    }

    /**
     * Validation minimum
     */
    protected function validateMin(string $prop, $value, $min): void
    {
        if (is_string($value) && strlen($value) < $min) {
            throw new ComponentException("La prop '{$prop}' doit contenir au moins {$min} caractères.");
        }
        if (is_numeric($value) && $value < $min) {
            throw new ComponentException("La prop '{$prop}' doit être supérieur ou égal à {$min}.");
        }
        if (is_array($value) && count($value) < $min) {
            throw new ComponentException("La prop '{$prop}' doit contenir au moins {$min} éléments.");
        }
    }

    /**
     * Validation maximum
     */
    protected function validateMax(string $prop, $value, $max): void
    {
        if (is_string($value) && strlen($value) > $max) {
            throw new ComponentException("La prop '{$prop}' ne peut pas dépasser {$max} caractères.");
        }
        if (is_numeric($value) && $value > $max) {
            throw new ComponentException("La prop '{$prop}' doit être inférieur ou égal à {$max}.");
        }
        if (is_array($value) && count($value) > $max) {
            throw new ComponentException("La prop '{$prop}' ne peut pas contenir plus de {$max} éléments.");
        }
    }

    /**
     * Validation in array
     */
    protected function validateIn(string $prop, $value, array $allowed): void
    {
        if (!in_array($value, $allowed, true)) {
            $allowedStr = implode(', ', $allowed);
            throw new ComponentException("La prop '{$prop}' doit être l'une des valeurs suivantes: {$allowedStr}.");
        }
    }

    /**
     * Validation instanceof
     */
    protected function validateInstanceOf(string $prop, $value, string $class): void
    {
        if (!($value instanceof $class)) {
            throw new ComponentException("La prop '{$prop}' doit être une instance de '{$class}'.");
        }
    }

    /**
     * Définit un slot
     */
    public function slot(string $name, $content): static
    {
        $this->slots[$name] = $content;
        return $this;
    }

    /**
     * Récupère une prop avec valeur par défaut
     */
    protected function prop(string $key, $default = null)
    {
        return $this->props[$key] ?? $default;
    }

    /**
     * Récupère toutes les props
     */
    protected function props(): array
    {
        return $this->props;
    }

    /**
     * Vérifie si un tableau est associatif
     */
    protected function isAssociativeArray(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Transforme les props pour la sérialisation
     */
    public function toArray(): array
    {
        return [
            'component' => static::class,
            'props' => $this->props,
            'slots' => $this->slots
        ];
    }

    /**
     * Sérialise le composant en JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
