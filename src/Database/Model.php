<?php

declare(strict_types=1);

namespace IronFlow\Database;

use DateTime;
use Exception;
use IronFlow\Database\Collection;
use IronFlow\Database\Connection;
use IronFlow\Database\Iron\Query\Builder;
use IronFlow\Database\Iron\Relations\HasManyThrough;
use IronFlow\Database\Iron\Relations\BelongsTo;
use IronFlow\Database\Iron\Relations\BelongsToMany;
use IronFlow\Database\Iron\Relations\HasMany;
use IronFlow\Database\Iron\Relations\HasOne;
use IronFlow\Database\Iron\Relations\MorphTo;
use PDOException;

/**
 * Classe de base pour tous les modèles
 * 
 * Cette classe fournit les fonctionnalités ORM de base pour interagir
 * avec la base de données de manière orientée objet.
 */
abstract class Model
{
   protected static string $table;
   protected static string $primaryKey = 'id';
   protected array $fillable = [];
   protected array $hidden = [];
   protected array $casts = [];
   protected array $dates = [];
   protected array $relations = [];
   protected array $attributes = [];
   protected array $original = [];
   protected bool $exists = false;
   protected static array $globalScopes = [];
   protected static bool $useCache = true;
   protected static int $cacheTimeout = 3600; // 1 heure
   protected static ?Connection $connection = null;

   protected static array $eventCallbacks = [
      'creating' => [],
      'created' => [],
      'updating' => [],
      'updated' => [],
      'deleting' => [],
      'deleted' => [],
      'saving' => [],
      'saved' => [],
   ];

   /**
    * Définit la connexion à la base de données pour le modèle
    * 
    * @param Connection $connection Instance de la connexion
    * @return void
    */
   public static function setConnection(Connection $connection): void
   {
      static::$connection = $connection;
   }

   /**
    * Récupère la connexion à la base de données
    * 
    * @return Connection
    */
   public static function getConnection(): Connection
   {
      if (static::$connection === null) {
         static::$connection = Connection::getInstance();
      }
      return static::$connection;
   }

   /**
    * Constructeur
    * 
    * @param array $attributes Attributs initiaux du modèle
    */
   public function __construct(array $attributes = [])
   {
      $this->fill($attributes);
      $this->original = $this->attributes;
   }

   /**
    * Remplit le modèle avec un tableau d'attributs
    * 
    * @param array $attributes Les attributs à remplir
    * @return self
    */
   public function fill(array $attributes): self
   {
      foreach ($attributes as $key => $value) {
         $this->setAttribute($key, $value);
      }
      return $this;
   }

   /**
    * Récuperer toutes données visible de $this->getModel()
    * @return array
    */
   public function data(): array
   {
      $data = [];

      foreach ($this->attributes as $key => $value) {
         if (in_array(strtolower($key), $this->fillable)) {
            $data[$key] = $value;
         }
      }

      return $data;
   }

   /**
    * Définit un attribut sur le modèle
    * 
    * @param string $key Nom de l'attribut
    * @param mixed $value Valeur de l'attribut
    */
   public function setAttribute(string $key, $value): void
   {
      if ($this->hasSetMutator($key)) {
         $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
         $value = $this->$method($value);
      }

      if (in_array($key, $this->dates)) {
         $value = $this->asDateTime($value);
      }

      if (isset($this->casts[$key])) {
         $value = $this->castAttribute($key, $value);
      }

      $this->attributes[$key] = $value;
   }

   /**
    * Récupère un attribut du modèle
    * 
    * @param string $key Nom de l'attribut
    * @return mixed Valeur de l'attribut
    */
   public function getAttribute(string $key)
   {
      if (array_key_exists($key, $this->attributes)) {
         return $this->attributes[$key];
      }

      if ($this->hasGetMutator($key)) {
         $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
         return $this->$method();
      }

      if (method_exists($this, $key)) {
         return $this->$key();
      }

      return null;
   }

   /**
    * Obtient le nom de la table du modèle
    * 
    * @return string Nom de la table
    */
   public function getTable(): string
   {
      return static::$table;
   }

   /**
    * Vérifie si le modèle a un accesseur pour l'attribut spécifié
    */
   protected function hasGetMutator(string $key): bool
   {
      return method_exists($this, 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
   }

   /**
    * Vérifie si le modèle a un mutateur pour l'attribut spécifié
    */
   protected function hasSetMutator(string $key): bool
   {
      return method_exists($this, 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
   }

   /**
    * Convertit un attribut selon son type de cast
    */
   protected function castAttribute(string $key, $value)
   {
      $type = $this->casts[$key];

      switch ($type) {
         case 'int':
         case 'integer':
            return (int) $value;
         case 'real':
         case 'float':
         case 'double':
            return (float) $value;
         case 'string':
            return (string) $value;
         case 'bool':
         case 'boolean':
            return (bool) $value;
         case 'array':
            return json_decode($value, true);
         case 'json':
            return json_decode($value, true);
         case 'object':
            return json_decode($value);
         case 'collection':
            return collect(json_decode($value, true));
         case 'datetime':
            return $this->asDateTime($value);
         default:
            return $value;
      }
   }

   /**
    * Convertit une valeur en objet DateTime
    */
   protected function asDateTime($value): bool|DateTime
   {
      if ($value instanceof DateTime) {
         return $value;
      }

      if (is_numeric($value)) {
         return DateTime::createFromFormat('U', (string) $value);
      }

      return new DateTime($value);
   }

   /**
    * Enregistre le modèle dans la base de données
    * 
    * @return bool Succès de l'opération
    */
   public function save(): bool
   {
      if (!$this->fireEvent('saving')) {
         return false;
      }

      $saved = false;

      if (isset($this->attributes[static::$primaryKey])) {
         if (!$this->fireEvent('updating')) {
            return false;
         }
         $saved = static::update($this->attributes);
         if ($saved) {
            $this->fireEvent('updated');
         }
      } else {
         if (!$this->fireEvent('creating')) {
            return false;
         }
         $newInstance = static::create($this->attributes);
         if ($newInstance) {
            $this->fill((array)$newInstance->attributes);
            $saved = true;
            $this->fireEvent('created');
         }
      }

      if ($saved) {
         $this->fireEvent('saved');
      }

      return $saved;
   }

   public function remove(): bool
   {
      if (isset($this->attributes[static::$primaryKey])) {
         $this->query()->where(static::$primaryKey, '=', $this->attributes[static::$primaryKey])->delete();
         return true;
      }

      return false;
   }

   /**
    * Définit les timestamps sur un tableau de données
    */
   protected static function setTimestamps(array &$data): void
   {
      $currentTimestamp = now()->getTimestamp();

      if (!isset($data['created_at'])) {
         $data['created_at'] = $currentTimestamp;
      }
      $data['updated_at'] = $currentTimestamp;
   }

   /**
    * Crée un nouvel enregistrement dans la base de données
    * 
    * @param array $data Données à insérer
    * @return static Nouvelle instance du modèle
    */
   public static function create(array $data): static
   {
      self::setTimestamps($data);

      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";

      try {
         static::getConnection()->execute($sql, $data);
         // Récupérer l'ID de l'enregistrement nouvellement créé
         $data[static::$primaryKey] = static::getConnection()->lastInsertId();

         $instance = new static();
         $instance->fill($data);
         $instance->exists = true;

         return $instance;
      } catch (PDOException $e) {
         throw new Exception("Erreur de base de données : " . $e->getMessage());
      }
   }

   /**
    * Crée plusieurs enregistrements dans la base de données
    * 
    * @param array $data Tableau de données à insérer
    * @return Collection Collection d'instances du modèle
    */
   public static function createMany(array $data): Collection
   {
      $records = array_map(
         fn($item): Model => static::create($item),
         $data
      );

      return new Collection($records);
   }

   /**
    * Met à jour un enregistrement existant dans la base de données
    * 
    * @param array $data Données à mettre à jour
    * @return bool Succès de l'opération
    */
   public static function update(array $data): bool
   {
      if (!isset($data[static::$primaryKey])) {
         throw new Exception("Clé primaire non définie pour l'opération de mise à jour");
      }

      // Définir le timestamp mis à jour
      $data['updated_at'] = now()->toDateTimeLocalString();

      $sets = [];
      foreach (array_keys($data) as $key) {
         if ($key !== static::$primaryKey) {
            $sets[] = "$key = :$key";
         }
      }

      $sql = "UPDATE " . static::$table . " SET " . implode(', ', $sets) .
         " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;

      try {
         return static::getConnection()->execute($sql, $data) > 0;
      } catch (PDOException $e) {
         throw new Exception("Erreur de base de données : " . $e->getMessage());
      }
   }

   /**
    * Supprime un enregistrement de la base de données
    * 
    * @param string|int|array $id Identifiant de l'enregistrement à supprimer
    * @return bool Succès de l'opération
    */
   public static function delete(string|int|array $id): bool
   {
      if (is_array($id)) {
         $placeholders = implode(", ", array_map(fn($i) => ":$i", range(0, count($id) - 1)));
         $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " IN ($placeholders)";
         $params = array_combine(range(0, count($id) - 1), $id);
      } else {
         $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
         $params = ['id' => $id];
      }

      try {
         return static::getConnection()->execute($sql, $params) > 0;
      } catch (PDOException $e) {
         throw new Exception("Erreur de base de données : " . $e->getMessage());
      }
   }

   /**
    * Récupère tous les enregistrements de la table
    * 
    * @return Collection Collection d'instances du modèle
    */
   public static function all(): Collection
   {
      $sql = "SELECT * FROM " . static::$table;
      $results = static::getConnection()->query($sql);
      return new Collection(array_map(fn($result) => new static($result), $results));
   }

   /**
    * Récupère les valeurs d'une ou plusieurs colonnes
    * 
    * @param string|array $columns Colonnes à récupérer
    * @return Collection Collection de valeurs
    */
   public static function pluck(string|array $columns): Collection
   {
      if (is_array($columns)) {
         $sql = "SELECT " . implode(', ', $columns) . " FROM " . static::$table;
      } else {
         $sql = "SELECT $columns FROM " . static::$table;
      }

      $results = static::getConnection()->query($sql);
      return new Collection($results);
   }

   /**
    * Traite les enregistrements par lots
    * 
    * @param int $size Taille du lot
    * @param callable $callback Fonction de rappel pour traiter chaque lot
    */
   public static function chunk(int $size, callable $callback): void
   {
      $offset = 0;
      do {
         $sql = "SELECT * FROM " . static::$table . " LIMIT :limit OFFSET :offset";
         $results = static::getConnection()->query($sql, [
            'limit' => $size,
            'offset' => $offset
         ]);

         if (empty($results)) {
            break;
         }

         $models = array_map(fn($result) => new static($result), $results);
         $callback($models);
         $offset += $size;
      } while (count($results) === $size);
   }

   /**
    * Trouve un enregistrement par son identifiant
    * 
    * @param mixed $id Identifiant de l'enregistrement
    * @return static|null Instance du modèle ou null si non trouvé
    */
   public static function find($id): ?static
   {
      $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1";
      $results = static::getConnection()->query($sql, ['id' => $id]);
      return $results ? new static($results[0]) : null;
   }

   /**
    * Trouve un enregistrement ou lance une exception
    * 
    * @param mixed $id Identifiant de l'enregistrement
    * @return static Instance du modèle
    * @throws Exception Si l'enregistrement n'est pas trouvé
    */
   public static function findOrFail($id): static
   {
      $model = static::find($id);
      if (!$model) {
         $className = static::class;
         throw new Exception("Le modèle {$className} avec l'ID {$id} n'a pas été trouvé.");
      }
      return $model;
   }

   public static function findOrCreate($id, $data = []): static
   {
      $model = static::find($id);
      if (!$model) {
         $className = static::class;
         $data['id'] = $id;
         $model = new $className($data);
         $model->save();
      }

      return $model;
   }

   /**
    * Commence une requête avec une condition where
    * 
    * @param string $column Colonne
    * @param mixed $value Valeur
    * @return Builder Builder de requête
    */
   public static function where($column, $value): Builder
   {
      return static::query()->where($column, $value);
   }

   /**
    * Récupère tous les enregistrements supprimés
    * 
    * @return array
    */
   public static function onlyTrashed(): array
   {
      $sql = "SELECT * FROM " . static::$table . " WHERE deleted_at IS NOT NULL";
      return static::getConnection()->query($sql);
   }

   /**
    * Restaure un enregistrement supprimé
    * 
    * @param int|string $id ID de l'enregistrement
    * @return bool
    */
   public static function restore($id): bool
   {
      $sql = "UPDATE " . static::$table . " SET deleted_at = NULL WHERE id = :id";
      return static::getConnection()->execute($sql, ['id' => $id]) > 0;
   }

   /**
    * Compte le nombre d'enregistrements
    * 
    * @return int
    */
   public static function count(): int
   {
      $sql = "SELECT COUNT(*) as count FROM " . static::$table;
      $result = static::getConnection()->query($sql);
      return (int) $result[0]['count'];
   }

   /**
    * Pagine les résultats
    * 
    * @param int $page Numéro de page
    * @param int $perPage Nombre d'éléments par page
    * @return array{data: Collection, total: int, current_page: int, per_page: int, last_page: int}
    */
   public static function paginate(int $page = 1, int $perPage = 10): array
   {
      $offset = ($page - 1) * $perPage;
      $sql = "SELECT * FROM " . static::$table . " LIMIT :limit OFFSET :offset";
      $results = static::getConnection()->query($sql, [
         'limit' => $perPage,
         'offset' => $offset
      ]);

      $total = static::count();
      $lastPage = (int) ceil($total / $perPage);

      return [
         'data' => new Collection(array_map(fn($result) => new static($result), $results)),
         'total' => $total,
         'current_page' => $page,
         'per_page' => $perPage,
         'last_page' => $lastPage,
      ];
   }

   /**
    * Pagine les résultats avec des options avancées
    * 
    * @param int $page Numéro de page
    * @param int $perPage Nombre d'éléments par page
    * @param array $columns Colonnes à sélectionner
    * @param string $pageName Nom du paramètre de page
    * @param array $options Options supplémentaires
    * @return array{data: Collection, total: int, current_page: int, per_page: int, last_page: int, from: int, to: int}
    */
   public static function paginateAdvanced(
      int $page = 1,
      int $perPage = 10,
      array $columns = ['*'],
      string $pageName = 'page',
      array $options = []
   ): array {
      $offset = ($page - 1) * $perPage;
      $columns = implode(', ', $columns);
      $sql = "SELECT {$columns} FROM " . static::$table;

      if (isset($options['where'])) {
         $sql .= " WHERE " . $options['where'];
      }

      if (isset($options['orderBy'])) {
         $sql .= " ORDER BY " . $options['orderBy'];
      }

      $sql .= " LIMIT :limit OFFSET :offset";
      $results = static::getConnection()->query($sql, [
         'limit' => $perPage,
         'offset' => $offset
      ] + ($options['params'] ?? []));

      $total = static::count();
      $lastPage = (int) ceil($total / $perPage);

      return [
         'data' => new Collection(array_map(fn($result) => new static($result), $results)),
         'total' => $total,
         'current_page' => $page,
         'per_page' => $perPage,
         'last_page' => $lastPage,
         'from' => $offset + 1,
         'to' => min($offset + $perPage, $total),
      ];
   }

   /**
    * Met à jour plusieurs enregistrements en une seule requête
    * 
    * @param array $values Valeurs à mettre à jour
    * @param array $conditions Conditions pour la mise à jour
    * @return int Nombre d'enregistrements mis à jour
    */
   public static function bulkUpdate(array $values, array $conditions): int
   {
      $sets = [];
      $params = [];

      foreach ($values as $key => $value) {
         $sets[] = "$key = :$key";
         $params[$key] = $value;
      }

      $where = [];
      foreach ($conditions as $key => $value) {
         $where[] = "$key = :where_$key";
         $params["where_$key"] = $value;
      }

      $sql = "UPDATE " . static::$table .
         " SET " . implode(', ', $sets) .
         " WHERE " . implode(' AND ', $where);

      try {
         return static::getConnection()->execute($sql, $params);
      } catch (PDOException $e) {
         throw new Exception("Erreur de base de données : " . $e->getMessage());
      }
   }

   /**
    * Ajoute un scope global au modèle
    * 
    * @param string $name Nom du scope
    * @param callable $callback Fonction du scope
    */
   public static function addGlobalScope(string $name, callable $callback): void
   {
      static::$globalScopes[$name] = $callback;
   }

   /**
    * Supprime un scope global du modèle
    * 
    * @param string $name Nom du scope
    */
   public static function removeGlobalScope(string $name): void
   {
      unset(static::$globalScopes[$name]);
   }

   /**
    * Applique les scopes globaux à une requête
    * 
    * @param Builder $query Builder de requête
    * @return Builder
    */
   protected static function applyGlobalScopes(Builder $query): Builder
   {
      foreach (static::$globalScopes as $scope) {
         $scope($query);
      }
      return $query;
   }

   /**
    * Démarre une transaction
    */
   public static function beginTransaction(): void
   {
      static::getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    */
   public static function commit(): void
   {
      static::getConnection()->commit();
   }

   /**
    * Annule une transaction
    */
   public static function rollback(): void
   {
      static::getConnection()->rollBack();
   }

   /**
    * Exécute une fonction dans une transaction
    * 
    * @param callable $callback Fonction à exécuter
    * @return mixed
    * @throws Exception
    */
   public static function transaction(callable $callback)
   {
      static::beginTransaction();

      try {
         $result = $callback();
         static::commit();
         return $result;
      } catch (Exception $e) {
         static::rollback();
         throw $e;
      }
   }

   /**
    * Active ou désactive le cache pour les requêtes
    */
   public static function useCache(bool $use = true): void
   {
      static::$useCache = $use;
   }

   /**
    * Définit le timeout du cache
    */
   public static function setCacheTimeout(int $seconds): void
   {
      static::$cacheTimeout = $seconds;
   }

   /**
    * Convertit le modèle en tableau
    * 
    * @return array
    */
   public function toArray(): array
   {
      $array = $this->attributes;

      // Exclure les attributs cachés
      foreach ($this->hidden as $hidden) {
         unset($array[$hidden]);
      }

      return $array;
   }

   /**
    * Initialise le modèle
    */
   protected static function boot(): void
   {
      // Cette méthode sera appelée lors de l'initialisation du modèle
      // et peut être étendue par les classes enfants
   }

   /**
    * Commence une nouvelle requête pour le modèle
    * 
    * @return Builder Builder de requête
    */
   public static function query(): Builder
   {
      $instance = new static();
      return new Builder($instance);
   }

   /**
    * Définit une relation HasOne
    * 
    * @param string $related Classe du modèle lié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $localKey Clé locale
    * @return HasOne Relation HasOne
    */
   public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
   {
      $foreignKey = $foreignKey ?? $this->getForeignKey();
      $localKey = $localKey ?? $this->getKeyName();

      $instance = new $related();

      return new HasOne(
         $instance,
         $this,
         $foreignKey,
         $localKey
      );
   }

   /**
    * Définit une relation HasOne
    * 
    * @param string $related Classe du modèle lié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $localKey Clé locale
    * @return MorphTo Relation MorphTo
    */
   public function morphTo(string $related, ?string $foreignKey = null, ?string $localKey = null): MorphTo
   {
      $foreignKey = $foreignKey ?? $this->getForeignKey();
      $localKey = $localKey ?? $this->getKeyName();

      $instance = new $related();

      return new MorphTo(
         $instance,
         $this,
         $foreignKey,
         $localKey
      );
   }

   /**
    * Définit une relation HasMany
    * 
    * @param string $related Classe du modèle lié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $localKey Clé locale
    * @return HasMany Relation HasMany
    */
   public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
   {
      $foreignKey = $foreignKey ?? $this->getForeignKey();
      $localKey = $localKey ?? $this->getKeyName();

      $instance = new $related();

      return new HasMany(
         $instance,
         $this,
         $foreignKey,
         $localKey
      );
   }

   /**
    * Définit une relation BelongsTo
    * 
    * @param string $related Classe du modèle lié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $ownerKey Clé du propriétaire
    * @return BelongsTo Relation BelongsTo
    */
   public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
   {
      $foreignKey = $foreignKey ?? $this->guessBelongsToForeignKey();
      $instance = new $related();
      $ownerKey = $ownerKey ?? $instance->getKeyName();

      return new BelongsTo(
         $instance,
         $this,
         $foreignKey,
         $ownerKey
      );
   }

   /**
    * Définit une relation BelongsToMany
    * 
    * @param string $related Classe du modèle lié
    * @param string|null $table Table pivot
    * @param string|null $foreignPivotKey Clé étrangère pivot
    * @param string|null $relatedPivotKey Clé pivot liée
    * @param string|null $parentKey Clé parente
    * @param string|null $relatedKey Clé liée
    * @return BelongsToMany Relation BelongsToMany
    */
   public function belongsToMany(
      string $related,
      ?string $table = null,
      ?string $foreignPivotKey = null,
      ?string $relatedPivotKey = null,
      ?string $parentKey = null,
      ?string $relatedKey = null
   ): BelongsToMany {
      $instance = new $related();

      $table = $table ?? $this->joiningTable($related);

      $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();

      $relatedPivotKey = $relatedPivotKey ?? $instance->getForeignKey();

      $parentKey = $parentKey ?? $this->getKeyName();

      $relatedKey = $relatedKey ?? $instance->getKeyName();

      return new BelongsToMany(
         $instance,
         $this,
         $table,
         $foreignPivotKey,
         $relatedPivotKey,
         $parentKey,
         $relatedKey
      );
   }

   /**
    * Définit une relation HasManyThrough
    * 
    * @param string $related Classe du modèle lié
    * @param string $through Classe du modèle intermédiaire
    * @param string|null $firstKey Première clé
    * @param string|null $secondKey Seconde clé
    * @param string|null $localKey Clé locale
    * @param string|null $secondLocalKey Seconde clé locale
    * @return HasManyThrough Relation HasManyThrough
    */
   public function hasManyThrough(
      string $related,
      string $through,
      ?string $firstKey = null,
      ?string $secondKey = null,
      ?string $localKey = null,
      ?string $secondLocalKey = null
   ): HasManyThrough {
      $through = new $through();

      $firstKey = $firstKey ?? $this->getForeignKey();

      $secondKey = $secondKey ?? $through->getForeignKey();

      $localKey = $localKey ?? $this->getKeyName();

      $secondLocalKey = $secondLocalKey ?? $through->getKeyName();

      $related = new $related();

      return new HasManyThrough(
         $related,
         $this,
         $through,
         $firstKey,
         $secondKey,
         $localKey,
         $secondLocalKey
      );
   }

   /**
    * Obtient la clé étrangère pour le modèle
    * 
    * @return string Nom de la clé étrangère
    */
   protected function getForeignKey(): string
   {
      $class = get_class($this);
      $parts = explode('\\', $class);
      $model = end($parts);
      return strtolower($model) . '_id';
   }

   /**
    * Obtient le nom de la clé primaire
    * 
    * @return string Nom de la clé primaire
    */
   protected function getKeyName(): string
   {
      return static::$primaryKey;
   }

   /**
    * Devine la clé étrangère pour une relation BelongsTo
    * 
    * @return string Nom de la clé étrangère
    */
   protected function guessBelongsToForeignKey(): string
   {
      // Pour une relation BelongsTo, la clé étrangère est sur le modèle courant
      $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
      $caller = $trace[2]['function'];

      // Enlève "belongsTo" du nom de la fonction
      $relation = str_replace('belongsTo', '', $caller);
      // Convertit en snake_case
      return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $relation)) . '_id';
   }

   /**
    * Obtient le nom de la table pivot pour une relation plusieurs-à-plusieurs
    * 
    * @param string $related Classe du modèle lié
    * @return string Nom de la table pivot
    */
   protected function joiningTable(string $related): string
   {
      // Extrait les noms de classe simples
      $models = [
         get_class($this),
         $related
      ];

      // Obtient juste les dernières parties des noms
      foreach ($models as &$model) {
         $parts = explode('\\', $model);
         $model = end($parts);
      }

      // Trie les modèles par ordre alphabétique
      sort($models);

      // Convertit en snake_case et joint avec un underscore
      return strtolower(
         preg_replace('/(?<!^)[A-Z]/', '_$0', $models[0]) . '_' .
            preg_replace('/(?<!^)[A-Z]/', '_$0', $models[1])
      );
   }

   /**
    * Commence une requête avec chargement avide des relations
    * 
    * @param string|array $relations Relations à charger
    * @return Builder Builder de requête
    */
   public static function with($relations): Builder
   {
      return static::query()->with($relations);
   }

   /**
    * Définit une relation sur le modèle
    * 
    * @param string $relation Nom de la relation
    * @param mixed $value Valeur de la relation
    * @return self
    */
   public function setRelation(string $relation, $value): self
   {
      $this->relations[$relation] = $value;
      return $this;
   }

   /**
    * Obtient une relation du modèle
    * 
    * @param string $relation Nom de la relation
    * @return mixed Valeur de la relation
    */
   public function getRelation(string $relation)
   {
      if (isset($this->relations[$relation])) {
         return $this->relations[$relation];
      }

      if (method_exists($this, $relation)) {
         return $this->setRelation($relation, $this->$relation()->get());
      }

      return null;
   }

   /**
    * Vérifie si une clé est une relation
    * 
    * @param string $key Clé à vérifier
    * @return bool True si c'est une relation
    */
   public function isRelation(string $key): bool
   {
      return method_exists($this, $key);
   }

   /**
    * Accès magique aux attributs et relations
    */
   public function __get(string $key)
   {
      return $this->getAttribute($key) ?? $this->getRelation($key);
   }

   /**
    * Définition magique des attributs
    */
   public function __set(string $key, $value): void
   {
      $this->setAttribute($key, $value);
   }

   /**
    * Vérification magique de l'existence d'un attribut
    */
   public function __isset(string $key): bool
   {
      return isset($this->attributes[$key]) || isset($this->relations[$key]);
   }

   /**
    * Suppression magique d'un attribut
    */
   public function __unset(string $key): void
   {
      unset($this->attributes[$key], $this->relations[$key]);
   }

   /**
    * Gestion des appels de méthode dynamiques
    */
   public function __call(string $method, array $arguments): mixed
   {
      if (method_exists($this, $method)) {
         return $this->$method(...$arguments);
      }

      throw new Exception("Method {$method} does not exist");
   }

   /**
    * Représentation en chaîne du modèle
    */
   public function __toString(): string
   {
      return json_encode($this->toArray());
   }

   /**
    * Récupère un résultat depuis le cache ou exécute la requête
    * 
    * @param string $key Clé de cache
    * @param callable $callback Requête à exécuter si pas en cache
    * @return mixed
    */
   protected static function remember(string $key, callable $callback)
   {
      if (!static::$useCache) {
         return $callback();
      }

      $cacheKey = static::class . ':' . $key;
      $cache = static::getConnection()->getCache();

      if ($cache[$cacheKey]) {
         return $cache[$cacheKey];
      }

      $result = $callback();
      $cache[$cacheKey]['data'] = $result;
      $cache[$cacheKey]['timeout'] = static::$cacheTimeout;

      return $result;
   }

   /**
    * Exécute une requête brute
    * 
    * @param string $sql Requête SQL
    * @param array $params Paramètres de la requête
    * @return Collection
    */
   public static function raw(string $sql, array $params = []): Collection
   {
      $results = static::getConnection()->query($sql, $params);
      return new Collection(array_map(fn($result) => new static($result), $results));
   }

   /**
    * Crée un index sur une ou plusieurs colonnes
    * 
    * @param string|array $columns Colonnes à indexer
    * @param string|null $name Nom de l'index
    * @return bool
    */
   public static function createIndex($columns, ?string $name = null): bool
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $name = $name ?? static::$table . '_' . implode('_', $columns) . '_index';

      $sql = "CREATE INDEX IF NOT EXISTS $name ON " . static::$table . " (" . implode(', ', $columns) . ")";

      try {
         return (bool)static::getConnection()->execute($sql);
      } catch (PDOException $e) {
         throw new Exception("Erreur lors de la création de l'index : " . $e->getMessage());
      }
   }

   /**
    * Effectue une agrégation sur une colonne
    * 
    * @param string $column Colonne à agréger
    * @param string $function Fonction d'agrégation (COUNT, SUM, AVG, etc.)
    * @param array $conditions Conditions WHERE
    * @return mixed
    */
   public static function aggregate(string $column, string $function, array $conditions = [])
   {
      $sql = "SELECT $function($column) as result FROM " . static::$table;

      if (!empty($conditions)) {
         $where = [];
         $params = [];
         foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
            $params[$key] = $value;
         }
         $sql .= " WHERE " . implode(' AND ', $where);
         $result = static::getConnection()->query($sql, $params);
      } else {
         $result = static::getConnection()->query($sql);
      }

      return $result[0]['result'] ?? null;
   }

   /**
    * Recherche fulltext dans les colonnes spécifiées
    * 
    * @param array $columns Colonnes à rechercher
    * @param string $search Terme de recherche
    * @return Collection
    */
   public static function search(array $columns, string $search): Collection
   {
      $columnList = implode(', ', $columns);
      $sql = "SELECT *, MATCH ($columnList) AGAINST (:search IN BOOLEAN MODE) as relevance 
              FROM " . static::$table . " 
              WHERE MATCH ($columnList) AGAINST (:search IN BOOLEAN MODE)
              ORDER BY relevance DESC";

      $results = static::getConnection()->query($sql, ['search' => $search]);
      return new Collection(array_map(fn($result) => new static($result), $results));
   }

   /**
    * Clone un enregistrement
    * 
    * @param array $attributes Attributs à modifier dans la copie
    * @return static|null
    */
   public function replicate(array $attributes = []): ?static
   {
      $clone = new static($this->attributes);
      unset($clone->attributes[static::$primaryKey]);

      foreach ($attributes as $key => $value) {
         $clone->setAttribute($key, $value);
      }

      $clone->save();
      return $clone;
   }

   /**
    * Rafraîchit le modèle depuis la base de données
    * 
    * @return static|null
    */
   public function refresh(): ?static
   {
      if (!isset($this->attributes[static::$primaryKey])) {
         return null;
      }

      $fresh = static::find($this->attributes[static::$primaryKey]);
      if ($fresh) {
         $this->attributes = $fresh->attributes;
         $this->original = $fresh->original;
      }

      return $this;
   }

   /**
    * Déclenche un événement du modèle (création, mise à jour, suppression, etc.)
    *
    * @param string $event Nom de l'événement (ex: 'creating', 'updating', ...)
    * @return bool Retourne false si un callback retourne explicitement false, sinon true
    */
   protected function fireEvent(string $event): bool
   {
      if (!isset(static::$eventCallbacks[$event])) {
         return true;
      }
      foreach (static::$eventCallbacks[$event] as $callback) {
         if (is_callable($callback) && $callback($this) === false) {
            return false;
         }
      }
      return true;
   }
}
