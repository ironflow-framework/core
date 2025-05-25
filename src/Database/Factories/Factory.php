<?php

declare(strict_types=1);

namespace IronFlow\Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use InvalidArgumentException;
use IronFlow\Database\Contracts\FactoryInterface;

/**
 * Classe de base pour les factories de modèles
 */
abstract class Factory implements FactoryInterface
{
    protected Generator $fake;
    protected string $model;
    protected array $states = [];
    protected array $defaultAttributes = [];
    protected array $attributes = [];

    public function __construct()
    {
        $this->fake = FakerFactory::create();
        $this->configure();
        $this->defineDefaults();
    }

    /**
     * Configure les états disponibles pour cette factory
     */
    abstract protected function configure(): void;

    /**
     * Définit les attributs par défaut en appelant la définition
     */
    public function defineDefaults(): void
    {
        $this->defaultAttributes = $this->definition();
    }

    /**
     * Définition des attributs par défaut à implémenter dans les factories enfants
     */
    abstract public function definition(): array;

    /**
     * Crée une nouvelle instance du modèle
     */
    public function make(array $attributes = [], ?string $state = null): object
    {
        $finalAttributes = array_merge(
            $this->defaultAttributes,
            $state ? $this->applyState($state) : [],
            $this->attributes,
            $attributes
        );

        $model = $this->model;
        return new $model($finalAttributes);
    }

    /**
     * Crée et persiste une nouvelle instance du modèle
     */
    public function create(array $attributes = [], ?string $state = null): object
    {
        $instance = $this->make($attributes, $state);
        $instance->save();
        return $instance;
    }

    /**
     * Crée plusieurs instances du modèle
     */
    public function createMany(?int $count = null, array $attributes = [], ?string $state = null): array
    {
        $count = $count ?? ($this->attributes['count'] ?? 1);
        $instances = [];
        for ($i = 0; $i < $count; $i++) {
            $instances[] = $this->create($attributes, $state);
        }
        return $instances;
    }

    public function count(int $count): self
    {
        $this->attributes['count'] = $count;
        return $this;
    }

    /**
     * Applique un état à la factory
     */
    protected function applyState(string $state): array
    {
        if (!isset($this->states[$state])) {
            throw new InvalidArgumentException("L'état '$state' n'existe pas.");
        }

        $stateCallback = $this->states[$state];
        return $stateCallback();
    }

    /**
     * Applique un état à la factory et retourne l'instance
     */
    public function state(string $state): self
    {
        $this->attributes = array_merge(
            $this->attributes,
            $this->applyState($state)
        );
        return $this;
    }

    /**
     * Retourne l'instance Faker
     */
    protected function getFaker(): Generator
    {
        return $this->fake;
    }

    public function withOverrides(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function withoutOverrides(array $attributes): self
    {
        $this->attributes = array_diff_key($this->attributes, array_flip($attributes));
        return $this;
    }
}
