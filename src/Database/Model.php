<?php

declare(strict_types=1);

namespace IronFlow\Database;

use Carbon\Carbon;
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
use PDO;
use PDOException;
use function PHPUnit\Framework\isInstanceOf;

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
    * Obtient l'instance de connexion à la base de données
    */
   private function getConnection(): PDO
   {
      return Connection::getInstance()->getConnection();
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
      if (isset($this->attributes[static::$primaryKey])) {
         // Mettre à jour l'enregistrement existant
         return static::update($this->attributes);
      } else {
         // Créer un nouvel enregistrement
         $newInstance = static::create($this->attributes);
         // Peupler l'instance actuelle avec les données nouvellement créées
         $this->fill((array)$newInstance->attributes);
         return true;
      }
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
    * @param array $data Données pour le nouvel enregistrement
    * @return static Nouvelle instance du modèle
    */
   public static function create(array $data): static
   {
      self::setTimestamps($data);

      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
      $stmt = (new static())->getConnection()->prepare($sql);

      try {
         $stmt->execute($data);
         // Récupérer l'ID de l'enregistrement nouvellement créé
         $data[static::$primaryKey] = (new static())->getConnection()->lastInsertId();

         $instance = new static();
         $instance->fill($data);
         $instance->exists = true;

         return $instance;
      } catch (PDOException $e) {
         // Log the error or handle it
         throw new Exception("Database error: " . $e->getMessage());
      }
   }

   public static function createMany(array $data): Collection
   {
      $records = array_map(
         fn($item): Model =>
         static::create($item),
         $data
      );

      return new Collection($records);
   }

   /**
    * Met à jour un enregistrement existant dans la base de données
    * 
    * @param array $data Donnée à mettre à jour
    * @return bool Succès de l'opération
    */
   public static function update(array $data): bool
   {
      if (!isset($data[static::$primaryKey])) {
         throw new Exception("Primary key not set for update operation");
      }

      // Définir le timestamp mis à jour
      $data['created_at'] = $data['created_at']->format('Y-m-d H:i:s');
      $data['updated_at'] = now()->toDateTimeLocalString();

      $sets = [];
      foreach (array_keys($data) as $key) {
         if ($key !== static::$primaryKey) {
            $sets[] = "$key = :$key";
         }
      }

      $query = "UPDATE " . static::$table . " SET " . implode(', ', $sets) .
         " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;

      $stmt = (new static())->getConnection()->prepare($query);


      try {
         return $stmt->execute($data);
      } catch (PDOException $e) {
         error_log($e->getMessage());
         throw new Exception("Database error: " . $e->getMessage());
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
         $query = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " IN (" . implode(", ", $id) . ")";
      } else {
         $query = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
      }

      $stmt = (new static())->getConnection()->prepare($query);
      $stmt->bindValue(':id', (int) $id);
      return $stmt->execute();
   }

   /**
    * Récupère tous les enregistrements de la table
    * 
    * @return Collection Collection d'instances du modèle
    */
   public static function all(): Collection
   {
      return static::query()->get();
   }

   /**
    * Récuperer les enregistrement d'une colonne de la table
    *
    * @param string|array $columns
    * @return Collection
    */
   public static function pluck(string|array $columns): Collection
   {
      if (is_array($columns)) {
         $sql = "SELECT " . implode(', ', $columns) . " FROM ";
      } else {
         $sql = "SELECT $columns FROM ";
      }

      $sql .= static::$table;
      $stmt = (new static())->getConnection()->query($sql);
      $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
      return new Collection($result);
   }

   public static function chunk(int $size, callable $callback): void
   {
      $offset = 0;
      do {
         $sql = "SELECT * FROM " . static::$table . " LIMIT $size OFFSET $offset";
         $stmt = (new static())->getConnection()->query($sql);
         $results = $stmt->fetchAll(PDO::FETCH_CLASS, static::class);

         if (empty($results)) {
            break;
         }

         $callback($results);
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
      return static::query()->find($id);
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

   public static function onlyTrashed(): array
   {
      $sql = "SELECT * FROM " . static::$table . " WHERE deleted_at IS NOT NULL";
      $stmt = (new static())->db->query($sql);
      return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
   }

   public static function restore($id): bool
   {
      $sql = "UPDATE " . static::$table . " SET deleted_at = NULL WHERE id = :id";
      $stmt = (new static())->db->prepare($sql);
      return $stmt->execute(['id' => $id]);
   }

   /**
    * Compte le nombre d'enregistrements
    * 
    * @return int Nombre d'enregistrements
    */
   public static function count(): int
   {
      $query = "SELECT COUNT(*) as count FROM " . static::$table;
      $stmt = (new static())->getConnection()->prepare($query);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return (int) $result['count'];
   }

   /**
    * Pagine les résultats de la requête
    * 
    * @param int $page Numéro de page
    * @param int $perPage Nombre d'éléments par page
    * @return array
    */
   public static function paginate(int $page = 1, int $perPage = 10): array
   {
      $offset = ($page - 1) * $perPage;
      $query = "SELECT * FROM " . static::$table . " LIMIT :limit OFFSET :offset";
      $stmt = (new static())->getConnection()->prepare($query);
      $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();

      return [
         'data' => new Collection($stmt->fetchAll(PDO::FETCH_ASSOC)),
         'total' => static::count(),
         'current_page' => $page,
         'per_page' => $perPage,
         'last_page' => ceil(static::count() / $perPage),
      ];
   }

   /**
    * Récupère le premier enregistrement de la table
    * 
    * @return static|null Instance du modèle ou null si aucun enregistrement
    */
   public static function first(): ?static
   {
      return static::query()->first();
   }

   /**
    * Vérifie si un enregistrement existe avec les conditions spécifiées
    * 
    * @param array $conditions Conditions à vérifier
    * @return bool True si l'enregistrement existe
    */
   public static function exists(array $conditions): bool
   {
      $query = "SELECT EXISTS (SELECT 1 FROM " . static::$table . " WHERE ";
      $whereClauses = [];
      foreach ($conditions as $column => $value) {
         $whereClauses[] = "$column = :$column";
      }
      $query .= implode(" AND ", $whereClauses) . ") as result";

      $stmt = (new static())->getConnection()->prepare($query);
      foreach ($conditions as $column => $value) {
         $stmt->bindValue(":$column", $value);
      }
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return (bool) $result['result'];
   }

   /**
    * Filtre les enregistrements selon des conditions
    * 
    * @param array $conditions Conditions de filtrage
    * @return Collection Collection d'instances du modèle
    */
   public static function filter(array $conditions): Collection
   {
      $query = "SELECT * FROM " . static::$table . " WHERE ";
      $whereClauses = [];
      foreach ($conditions as $column => $value) {
         $whereClauses[] = "$column = :$column";
      }
      $query .= implode(" AND ", $whereClauses);

      $stmt = (new static())->getConnection()->prepare($query);
      foreach ($conditions as $column => $value) {
         $stmt->bindValue(":$column", $value);
      }
      $stmt->execute();
      $results = $stmt->fetch(PDO::FETCH_ASSOC);

      $models = [];
      foreach ($results as $result) {

         $model = new static();
         $model->fill($result);
         $model->exists = true;
         $model->save();

         $models[] = $model;
      }


      return new Collection($models);
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
}
