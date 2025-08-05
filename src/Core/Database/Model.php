<?php

declare(strict_types=1);

namespace IronFlow\Core\Database;

use IronFlow\Core\Database\Database;
use IronFlow\Core\Database\QueryBuilder;
use IronFlow\Core\Database\Collection;
use IronFlow\Core\Database\Concerns\HasAttributes;
use IronFlow\Core\Database\Concerns\HasRelationships;
use IronFlow\Core\Database\Relations\HasOne;
use IronFlow\Core\Database\Relations\HasMany;
use IronFlow\Core\Database\Relations\BelongsTo;
use IronFlow\Core\Database\Relations\BelongsToMany;
use IronFlow\Core\Cache\CacheInterface;
use IronFlow\Core\Cache\MemoryCache;

/**
 * Model de base - Active Record Pattern
 * Version mise à jour pour compatibilité avec la nouvelle Database
 */
abstract class Model
{
    use HasAttributes, HasRelationships;

    /**
     * Connexion à la base de données
     */
    protected static ?Database $database = null;

    /**
     * Nom de la table
     */
    protected static string $table = '';

    /**
     * Clé primaire
     */
    protected string $primaryKey = 'id';

    /**
     * Attributs remplissables
     */
    protected array $fillable = [];

    /**
     * Attributs cachés lors de la sérialisation
     */
    protected array $hidden = [];

    /**
     * Attributs protégés (non modifiables via fill)
     */
    protected array $guarded = [];

    /**
     * Instance de cache
     */
    protected static ?CacheInterface $cache = null;

    /**
     * Durée de vie du cache en secondes
     */
    protected static int $cacheTtl = 300; // 5 minutes

    /**
     * Attributs du model
     */
    protected array $attributes = [];

    /**
     * Valeurs originales (pour détecter les changements)
     */
    protected array $original = [];

    /**
     * Relations chargées
     */
    protected array $relations = [];

    /**
     * Indique si le model existe en base
     */
    protected bool $exists = false;

    /**
     * Indique si le timestamps sont gérés automatiquement
     */
    protected bool $timestamps = true;

    /**
     * Nom des colonnes de timestamps
     */
    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';

    /**
     * Constructeur
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->initializeDatabase();
    }

    /**
     * Initialise la connexion à la base de données
     */
    protected function initializeDatabase(): void
    {
        if (static::$database === null) {
            static::$database = Database::getInstance();
            static::$cache = static::$cache ?? MemoryCache::getInstance();
        }
    }

    /**
     * Configure la base de données manuellement
     */
    public static function setDatabase(Database $database): void
    {
        static::$database = $database;
        static::$cache = MemoryCache::getInstance();
    }

    /**
     * Retourne le nom de la table
     */
    public static function getTable(): string
    {
        if (empty(static::$table)) {
            $className = (new \ReflectionClass(static::class))->getShortName();
            static::$table = strtolower($className) . 's';
        }
        return static::$table;
    }

    /**
     * Retourne l'instance de cache
     */
    protected static function getCache(): CacheInterface
    {
        return static::$cache ?? MemoryCache::getInstance();
    }

    /**
     * Crée un nouveau Query Builder
     */
    public static function query(): QueryBuilder
    {
        $instance = new static();
        $instance->initializeDatabase();
        return new QueryBuilder(static::$database->getConnection(), static::getTable());
    }

    /**
     * Trouve tous les enregistrements
     */
    public static function all(): Collection
    {
        $cacheKey = static::class . ':all';
        $cache = static::getCache();
        
        $results = $cache->get($cacheKey);
        if ($results === null) {
            $instance = new static();
            $instance->initializeDatabase();
            $records = static::$database->select("SELECT * FROM " . static::getTable());
            $results = collect($records)->map(fn($item) => static::newFromBuilder($item));
            $cache->set($cacheKey, $results, static::$cacheTtl);
        }
        
        return $results instanceof Collection ? $results : collect($results);
    }

    /**
     * Trouve un enregistrement par ID
     */
    public static function find(mixed $id): ?static
    {
        if ($id === null) {
            return null;
        }

        $cacheKey = static::class . ':find:' . $id;
        $cache = static::getCache();
        
        $result = $cache->get($cacheKey);
        if ($result === null) {
            $instance = new static();
            $instance->initializeDatabase();
            $record = static::$database->selectOne(
                "SELECT * FROM " . static::getTable() . " WHERE " . $instance->primaryKey . " = ?",
                [$id]
            );
            
            if ($record) {
                $result = static::newFromBuilder($record);
                $cache->set($cacheKey, $result, static::$cacheTtl);
            }
        }
        
        return $result;
    }

    /**
     * Trouve plusieurs enregistrements par IDs
     */
    public static function findMany(array $ids): Collection
    {
        if (empty($ids)) {
            return collect([]);
        }

        $instance = new static();
        $instance->initializeDatabase();
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $records = static::$database->select(
            "SELECT * FROM " . static::getTable() . " WHERE " . $instance->primaryKey . " IN ({$placeholders})",
            $ids
        );

        return collect($records)->map(fn($item) => static::newFromBuilder($item));
    }

    /**
     * WHERE simple
     */
    public static function where(string $column, string $operator = '=', mixed $value = null): Collection
    {
        // Si seulement 2 paramètres, le deuxième est la valeur
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $instance = new static();
        $instance->initializeDatabase();
        $records = static::$database->select(
            "SELECT * FROM " . static::getTable() . " WHERE {$column} {$operator} ?",
            [$value]
        );

        return collect($records)->map(fn($item) => static::newFromBuilder($item));
    }

    /**
     * Trouve le premier enregistrement
     */
    public static function first(): ?static
    {
        $instance = new static();
        $instance->initializeDatabase();
        $record = static::$database->selectOne("SELECT * FROM " . static::getTable() . " LIMIT 1");
        return $record ? static::newFromBuilder($record) : null;
    }

    /**
     * Trouve le premier ou échoue
     */
    public static function firstOrFail(): static
    {
        $result = static::first();
        if ($result === null) {
            throw new \RuntimeException('No results found for ' . static::class);
        }
        return $result;
    }

    /**
     * Trouve ou crée un enregistrement
     */
    public static function firstOrCreate(array $attributes, array $values = []): static
    {
        $instance = static::where(array_keys($attributes)[0], array_values($attributes)[0])->first();
        
        if ($instance === null) {
            return static::create(array_merge($attributes, $values));
        }
        
        return $instance;
    }

    /**
     * Met à jour ou crée un enregistrement
     */
    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $instance = static::where(array_keys($attributes)[0], array_values($attributes)[0])->first();
        
        if ($instance === null) {
            return static::create(array_merge($attributes, $values));
        }
        
        $instance->fill($values);
        $instance->save();
        
        return $instance;
    }

    /**
     * Crée un nouvel enregistrement
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Crée une instance depuis les données de la DB
     */
    public static function newFromBuilder(array $attributes): static
    {
        $model = new static();
        $model->setRawAttributes($attributes);
        $model->exists = true;
        $model->syncOriginal();
        return $model;
    }

    /**
     * Remplit les attributs
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Sauvegarde le modèle
     */
    public function save(): bool
    {
        $this->initializeDatabase();
        
        // Gestion des timestamps
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $this->setAttribute($this->createdAt, $now);
            }
            $this->setAttribute($this->updatedAt, $now);
        }
        
        $result = $this->exists ? $this->performUpdate() : $this->performInsert();
        
        if ($result) {
            $this->clearModelCache();
            $this->syncOriginal();
        }
        
        return $result;
    }

    /**
     * Supprime le modèle
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $this->initializeDatabase();
        
        $deleted = static::$database->delete(
            "DELETE FROM " . static::getTable() . " WHERE {$this->primaryKey} = ?",
            [$this->getKey()]
        ) > 0;

        if ($deleted) {
            $this->exists = false;
            $this->clearModelCache();
        }

        return $deleted;
    }

    /**
     * Met à jour des enregistrements
     */
    public static function updateWhere(array $conditions, array $values): int
    {
        $instance = new static();
        $instance->initializeDatabase();
        
        $whereClause = [];
        $bindings = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $setClause = [];
        foreach ($values as $column => $value) {
            $setClause[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $sql = "UPDATE " . static::getTable() . 
               " SET " . implode(', ', $setClause) . 
               " WHERE " . implode(' AND ', $whereClause);
        
        $result = static::$database->update($sql, $bindings);
        
        // Vider le cache après mise à jour
        static::getCache()->delete(static::class . ':all');
        
        return $result;
    }

    /**
     * Supprime des enregistrements
     */
    public static function deleteWhere(array $conditions): int
    {
        $instance = new static();
        $instance->initializeDatabase();
        
        $whereClause = [];
        $bindings = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $sql = "DELETE FROM " . static::getTable() . " WHERE " . implode(' AND ', $whereClause);
        $result = static::$database->delete($sql, $bindings);
        
        // Vider le cache après suppression
        static::getCache()->delete(static::class . ':all');
        
        return $result;
    }

    /**
     * Effectue une insertion
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getAttributesForInsert();

        if (empty($attributes)) {
            return true;
        }

        $columns = array_keys($attributes);
        $values = array_values($attributes);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';

        $sql = "INSERT INTO " . static::getTable() . 
               " (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        if (static::$database->insert($sql, $values)) {
            $this->setAttribute($this->primaryKey, static::$database->lastInsertId());
            $this->exists = true;
            return true;
        }

        return false;
    }

    /**
     * Effectue une mise à jour
     */
    protected function performUpdate(): bool
    {
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return true;
        }

        $setClause = [];
        $bindings = [];
        
        foreach ($dirty as $column => $value) {
            $setClause[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $bindings[] = $this->getKey();

        $sql = "UPDATE " . static::getTable() . 
               " SET " . implode(', ', $setClause) . 
               " WHERE {$this->primaryKey} = ?";

        return static::$database->update($sql, $bindings) > 0;
    }

    /**
     * Relations HasOne
     */
    protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    /**
     * Relations HasMany
     */
    protected function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    /**
     * Relations BelongsTo
     */
    protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    /**
     * Relations BelongsToMany
     */
    protected function belongsToMany(
        string $related, 
        ?string $table = null, 
        ?string $foreignPivotKey = null, 
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ): BelongsToMany {
        return new BelongsToMany($this, $related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

    /**
     * Charge les relations avec eager loading
     */
    public static function with(array $relations): QueryBuilder
    {
        // Cette méthode sera implémentée dans une version future
        // Pour l'instant, retourne le query builder normal
        return static::query();
    }

    /**
     * Charge une relation dynamiquement
     */
    public function load(string $relation): self
    {
        if (method_exists($this, $relation)) {
            $this->relations[$relation] = $this->$relation()->getResults();
        }
        return $this;
    }

    /**
     * Vide le cache du modèle
     */
    protected function clearModelCache(): void
    {
        $cache = static::getCache();
        $cache->delete(static::class . ':all');
        
        if ($this->getKey()) {
            $cache->delete(static::class . ':find:' . $this->getKey());
        }
    }

    /**
     * Exécute du code dans une transaction
     */
    public static function transaction(callable $callback)
    {
        $instance = new static();
        $instance->initializeDatabase();
        return static::$database->transaction($callback);
    }

    /**
     * Compte les enregistrements
     */
    public static function count(): int
    {
        $instance = new static();
        $instance->initializeDatabase();
        
        $result = static::$database->selectOne("SELECT COUNT(*) as count FROM " . static::getTable());
        return (int) $result['count'];
    }

    /**
     * Vérifie si des enregistrements existent
     */
    public static function exists(): bool
    {
        return static::count() > 0;
    }

    /**
     * Pagination simple
     */
    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        $instance = new static();
        $instance->initializeDatabase();
        
        $offset = ($page - 1) * $perPage;
        
        // Compte total
        $total = static::count();
        
        // Récupération des données
        $records = static::$database->select(
            "SELECT * FROM " . static::getTable() . " LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $items = collect($records)->map(fn($item) => static::newFromBuilder($item));
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Suppression en lot
     */
    public static function destroy(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $instance = new static();
        $instance->initializeDatabase();
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $deleted = static::$database->delete(
            "DELETE FROM " . static::getTable() . " WHERE " . $instance->primaryKey . " IN ({$placeholders})",
            $ids
        );

        // Vider le cache après suppression
        static::getCache()->delete(static::class . ':all');
        foreach ($ids as $id) {
            static::getCache()->delete(static::class . ':find:' . $id);
        }

        return $deleted;
    }

    /**
     * Insertion en lot
     */
    public static function insert(array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $instance = new static();
        $instance->initializeDatabase();

        // Assurer que tous les enregistrements ont les mêmes clés
        $columns = array_keys($data[0]);
        
        $values = [];
        $bindings = [];
        
        foreach ($data as $record) {
            $recordValues = [];
            foreach ($columns as $column) {
                $recordValues[] = '?';
                $bindings[] = $record[$column] ?? null;
            }
            $values[] = '(' . implode(',', $recordValues) . ')';
        }

        $sql = "INSERT INTO " . static::getTable() . 
               " (" . implode(', ', $columns) . ") VALUES " . implode(', ', $values);

        $result = static::$database->insert($sql, $bindings);
        
        // Vider le cache après insertion
        static::getCache()->delete(static::class . ':all');
        
        return $result;
    }

    // ===========================================
    // SCOPES (Portées de requête)
    // ===========================================

    /**
     * Ajoute une portée globale
     */
    protected static function addGlobalScope(string $name, \Closure $scope): void
    {
        // À implémenter dans une version future
    }

    /**
     * Applique une portée locale
     */
    public function scopeActive(QueryBuilder $query): QueryBuilder
    {
        return $query->where('active', '=',1);
    }

    // ===========================================
    // ÉVÉNEMENTS (Events)
    // ===========================================

    /**
     * Événements du modèle
     */
    protected static array $events = [
        'creating', 'created', 'updating', 'updated', 
        'saving', 'saved', 'deleting', 'deleted'
    ];

    /**
     * Déclenche un événement
     */
    protected function fireEvent(string $event): void
    {
        $method = 'on' . ucfirst($event);
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    // ===========================================
    // GETTERS ET SETTERS
    // ===========================================

    /**
     * Récupère un attribut
     */
    public function getAttribute(string $key): mixed
    {
        // Vérifier d'abord les relations chargées
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // Vérifier si c'est une relation définie
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof \IronFlow\Core\Database\Relations\Relation) {
                $this->relations[$key] = $relation->getResults();
                return $this->relations[$key];
            }
        }

        // Attribut normal
        return $this->attributes[$key] ?? null;
    }

    /**
     * Définit un attribut
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Récupère la clé primaire
     */
    public function getKey(): mixed
    {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * Récupère le nom de la clé primaire
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Vérifie si un attribut est remplissable
     */
    protected function isFillable(string $key): bool
    {
        // Si la liste des guarded n'est pas vide, vérifier qu'elle ne contient pas la clé
        if (!empty($this->guarded)) {
            return !in_array($key, $this->guarded) && $key !== $this->primaryKey;
        }

        // Si la liste fillable est vide, tout est remplissable (sauf la clé primaire)
        if (empty($this->fillable)) {
            return $key !== $this->primaryKey;
        }

        // Sinon, vérifier que la clé est dans fillable
        return in_array($key, $this->fillable);
    }

    /**
     * Définit les attributs bruts
     */
    protected function setRawAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Synchronise les valeurs originales
     */
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    /**
     * Récupère les attributs modifiés
     */
    protected function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        // Ne pas mettre à jour la clé primaire
        unset($dirty[$this->primaryKey]);
        
        return $dirty;
    }

    /**
     * Récupère les attributs pour l'insertion
     */
    protected function getAttributesForInsert(): array
    {
        $attributes = $this->attributes;
        
        // Retirer la clé primaire si elle est vide (auto-increment)
        if (empty($attributes[$this->primaryKey])) {
            unset($attributes[$this->primaryKey]);
        }
        
        return $attributes;
    }

    /**
     * Vérifie si le modèle a été modifié
     */
    public function isDirty(?string $key = null): bool
    {
        if ($key !== null) {
            return $this->isDirtyAttribute($key);
        }
        return !empty($this->getDirty());
    }

    /**
     * Vérifie si un attribut spécifique a été modifié
     */
    public function isDirtyAttribute(string $key): bool
    {
        return array_key_exists($key, $this->getDirty());
    }

    /**
     * Vérifie si le modèle est propre (non modifié)
     */
    public function isClean(?string $key = null): bool
    {
        return !$this->isDirty($key);
    }

    /**
     * Récupère la valeur originale d'un attribut
     */
    public function getOriginal(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->original;
        }
        return $this->original[$key] ?? null;
    }

    /**
     * Vérifie si un attribut a changé depuis la dernière sauvegarde
     */
    public function wasChanged(?string $key = null): bool
    {
        if ($key === null) {
            return !empty($this->getChanges());
        }
        return array_key_exists($key, $this->getChanges());
    }

    /**
     * Récupère les changements depuis la dernière sauvegarde
     */
    public function getChanges(): array
    {
        $changes = [];
        foreach ($this->attributes as $key => $value) {
            if (array_key_exists($key, $this->original) && $this->original[$key] !== $value) {
                $changes[$key] = $value;
            }
        }
        return $changes;
    }

    /**
     * Recharge le modèle depuis la base de données
     */
    public function fresh(): ?static
    {
        if (!$this->exists) {
            return null;
        }
        return static::find($this->getKey());
    }

    /**
     * Recharge les attributs du modèle
     */
    public function refresh(): self
    {
        if (!$this->exists) {
            return $this;
        }

        $fresh = $this->fresh();
        if ($fresh) {
            $this->attributes = $fresh->attributes;
            $this->relations = [];
            $this->syncOriginal();
        }

        return $this;
    }

    // ===========================================
    // SERIALISATION
    // ===========================================

    /**
     * Conversion vers array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;

        // Ajouter les relations chargées
        foreach ($this->relations as $key => $relation) {
            if ($relation instanceof Collection) {
                $attributes[$key] = $relation->map(fn($item) => $item->toArray())->toArray();
            } elseif ($relation instanceof Model) {
                $attributes[$key] = $relation->toArray();
            } else {
                $attributes[$key] = $relation;
            }
        }

        // Masquer les attributs cachés
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }

        return $attributes;
    }

    /**
     * Conversion vers JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | $options);
    }

    /**
     * Sérialisation pour json_encode
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ===========================================
    // MÉTHODES MAGIQUES
    // ===========================================

    /**
     * Méthodes magiques pour l'accès aux attributs
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->getAttribute($key) !== null;
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
        unset($this->relations[$key]);
    }

    /**
     * Conversion automatique en string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Appel de méthodes de scope
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // Vérifier si c'est un scope
        if (str_starts_with($method, 'scope')) {
            $query = static::query();
            $scopeMethod = 'scope' . ucfirst(substr($method, 5));
            $instance = new static();
            
            if (method_exists($instance, $scopeMethod)) {
                return $instance->$scopeMethod($query, ...$arguments);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }

    /**
     * Clonage du modèle
     */
    public function __clone()
    {
        $this->exists = false;
        $this->setAttribute($this->primaryKey, null);
        $this->relations = [];
        $this->original = [];
    }
}