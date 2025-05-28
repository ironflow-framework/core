<?php

declare(strict_types=1);

namespace IronFlow\Database\Factories;

use Closure;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use InvalidArgumentException;
use IronFlow\Database\Contracts\FactoryInterface;
use IronFlow\Database\Exceptions\FactoryException;

/**
 * Classe de base pour les factories de modèles
 */
abstract class Factory implements FactoryInterface
{
    protected Generator $faker;
    protected string $model;
    protected array $states = [];
    protected array $attributes = [];
    protected array $afterMaking = [];
    protected array $afterCreating = [];
    protected ?Closure $connection = null;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
        $this->configure();
    }

    /**
     * Configure la factory (à implémenter dans les classes enfants)
     */
    protected function configure(): void
    {
        // À surcharger dans les classes enfants
    }

    /**
     * Définition des attributs par défaut (à implémenter dans les classes enfants)
     */
    abstract public function definition(): array;

    /**
     * Crée une nouvelle instance du modèle sans la persister
     */
    public function make(array $attributes = []): object
    {
        $finalAttributes = $this->mergeAttributes($attributes);
        $instance = $this->newModel($finalAttributes);

        return $this->callAfterMaking($instance);
    }

    /**
     * Crée et persiste une nouvelle instance du modèle
     */
    public function create(array $attributes = []): object
    {
        $instance = $this->make($attributes);

        if (method_exists($instance, 'save')) {
            $instance->save();
        } else {
            throw new FactoryException(
                "Model {$this->model} does not have a save() method"
            );
        }

        return $this->callAfterCreating($instance);
    }

    /**
     * Crée plusieurs instances
     */
    public function count(int $count): static
    {
        return $this->times($count);
    }

    /**
     * Crée plusieurs instances
     */
    public function times(int $count): static
    {
        $factory = clone $this;
        $factory->attributes['__count'] = $count;
        return $factory;
    }

    /**
     * Crée plusieurs instances du modèle
     */
    public function createMany(int $count, array $attributes = []): array
    {
        $instances = [];
        for ($i = 0; $i < $count; $i++) {
            $instances[] = $this->create($attributes);
        }
        return $instances;
    }

    /**
     * Applique un état à la factory
     */
    public function state(string $state): static
    {
        $factory = clone $this;

        if (!isset($this->states[$state])) {
            throw new InvalidArgumentException("State '{$state}' does not exist");
        }

        $stateAttributes = $this->resolveStateAttributes($state);
        $factory->attributes = array_merge($factory->attributes, $stateAttributes);

        return $factory;
    }

    /**
     * Surcharge des attributs
     */
    public function with(array $attributes): static
    {
        $factory = clone $this;
        $factory->attributes = array_merge($factory->attributes, $attributes);
        return $factory;
    }

    /**
     * Supprime des attributs
     */
    public function without(array $keys): static
    {
        $factory = clone $this;
        foreach ($keys as $key) {
            unset($factory->attributes[$key]);
        }
        return $factory;
    }

    /**
     * Définit un callback à exécuter après la création d'une instance
     */
    public function afterMaking(Closure $callback): static
    {
        $factory = clone $this;
        $factory->afterMaking[] = $callback;
        return $factory;
    }

    /**
     * Définit un callback à exécuter après la persistence d'une instance
     */
    public function afterCreating(Closure $callback): static
    {
        $factory = clone $this;
        $factory->afterCreating[] = $callback;
        return $factory;
    }

    /**
     * Définit une relation
     */
    public function for(object $related, ?string $relationship = null): static
    {
        $factory = clone $this;

        if ($relationship) {
            $factory->attributes[$relationship] = $related;
        } else {
            // Déduction automatique du nom de la relation
            $relationshipName = $this->guessRelationshipName($related);
            $factory->attributes[$relationshipName] = $related;
        }

        return $factory;
    }

    /**
     * Crée des instances liées
     */
    public function has(string $factory, int $count = 1, array $attributes = []): static
    {
        $factoryInstance = clone $this;

        $factoryInstance->afterCreating[] = function ($model) use ($factory, $count, $attributes) {
            $relatedFactory = new $factory();
            $relatedModels = $relatedFactory->count($count)->create($attributes);

            // Associer les modèles (dépend de votre ORM)
            if (method_exists($model, 'associate')) {
                foreach ($relatedModels as $relatedModel) {
                    $model->associate($relatedModel);
                }
            }
        };

        return $factoryInstance;
    }

    /**
     * Définit un état personnalisé
     */
    protected function state(string $name, Closure|array $attributes): void
    {
        $this->states[$name] = $attributes;
    }

    /**
     * Fusionne les attributs avec la définition par défaut
     */
    protected function mergeAttributes(array $attributes): array
    {
        $definition = $this->definition();
        $factoryAttributes = $this->attributes;

        // Résoudre les closures dans les attributs
        foreach ($factoryAttributes as $key => $value) {
            if ($value instanceof Closure) {
                $factoryAttributes[$key] = $value($this->faker);
            }
        }

        return array_merge($definition, $factoryAttributes, $attributes);
    }

    /**
     * Résout les attributs d'un état
     */
    protected function resolveStateAttributes(string $state): array
    {
        $stateDefinition = $this->states[$state];

        if ($stateDefinition instanceof Closure) {
            return $stateDefinition($this->faker);
        }

        if (is_array($stateDefinition)) {
            return $stateDefinition;
        }

        throw new FactoryException("Invalid state definition for '{$state}'");
    }

    /**
     * Crée une nouvelle instance du modèle
     */
    protected function newModel(array $attributes): object
    {
        if (!class_exists($this->model)) {
            throw new FactoryException("Model class {$this->model} does not exist");
        }

        return new $this->model($attributes);
    }

    /**
     * Exécute les callbacks après création d'instance
     */
    protected function callAfterMaking(object $instance): object
    {
        foreach ($this->afterMaking as $callback) {
            $callback($instance, $this->faker);
        }

        return $instance;
    }

    /**
     * Exécute les callbacks après persistence
     */
    protected function callAfterCreating(object $instance): object
    {
        foreach ($this->afterCreating as $callback) {
            $callback($instance, $this->faker);
        }

        return $instance;
    }

    /**
     * Devine le nom de la relation
     */
    protected function guessRelationshipName(object $related): string
    {
        $className = get_class($related);
        $baseName = basename(str_replace('\\', '/', $className));
        return strtolower($baseName) . '_id';
    }

    /**
     * Retourne l'instance Faker
     */
    protected function faker(): Generator
    {
        return $this->faker;
    }

    /**
     * Méthode magique pour gérer les appels de méthodes dynamiques
     */
    public function __call(string $method, array $parameters)
    {
        // Support pour les états dynamiques
        if (isset($this->states[$method])) {
            return $this->state($method);
        }

        throw new InvalidArgumentException("Method {$method} does not exist");
    }

    /**
     * Clone la factory
     */
    public function __clone()
    {
        $this->attributes = [];
        $this->afterMaking = [];
        $this->afterCreating = [];
    }
}
