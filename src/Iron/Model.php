<?php

declare(strict_types=1);

namespace IronFlow\Iron;

use DateTime;
use Exception;
use IronFlow\Iron\Collection;
use IronFlow\Iron\Query\Builder;
use IronFlow\Iron\Relations\HasManyThrough;
use IronFlow\Iron\Relations\BelongsTo;
use IronFlow\Iron\Relations\BelongsToMany;
use IronFlow\Iron\Relations\HasMany;
use IronFlow\Iron\Relations\HasOne;
use IronFlow\Iron\Connection;
use PDO;
use PDOException;

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

   public function __construct(array $attributes = [])
   {
      $this->fill($attributes);
      $this->original = $this->attributes;
   }

   public function fill(array $attributes): self
   {
      foreach ($attributes as $key => $value) {
         $this->setAttribute($key, $value);
      }
      return $this;
   }

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

   public function getTable(): string
   {
      return static::$table;
   }

   protected function hasGetMutator(string $key): bool
   {
      return method_exists($this, 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
   }

   protected function hasSetMutator(string $key): bool
   {
      return method_exists($this, 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
   }

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
         default:
            return $value;
      }
   }

   private function getConnection(): PDO
   {
      return Connection::getInstance()->getConnection();
   }

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

   public function save(): bool
   {
      if (isset($this->id)) {
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

   protected static function setTimestamps(array &$data): void
   {
      $currentTimestamp = self::formatDateToString(new DateTime());

      if (!isset($data['created_at'])) {
         $data['created_at'] = $currentTimestamp;
      }
      $data['updated_at'] = $currentTimestamp;
   }


   public static function create(array $data): Model
   {
      self::setTimestamps($data);

      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
      $stmt = (new static())->getConnection()->prepare($sql);

      try {
         $stmt->execute($data);
         // Récupérer l'ID de l'enregistrement nouvellement créé
         $data['id'] = (new static())->getConnection()->lastInsertId();
         return (new static())->fill($data);
      } catch (PDOException $e) {
         // Log the error or handle it
         throw new Exception("Database error: " . $e->getMessage());
      }
   }

   public static function update(array $attributes): bool
   {
      $query = "UPDATE " . static::$table . " SET " . implode(', ', array_map(function ($key) {
         return $key . " = :" . $key;
      }, array_keys($attributes))) . " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;

      $stmt = (new static())->getConnection()->prepare($query);
      foreach ($attributes as $key => $value) {
         $stmt->bindValue(':' . $key, $value);
      }
      return $stmt->execute();
   }

   public static function delete($id): bool
   {
      $query = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;
      $stmt = Connection::getInstance()->getConnection()->prepare($query);
      $stmt->bindValue(':' . static::$primaryKey, $id);
      return $stmt->execute();
   }

   public static function all(): Collection
   {
      return static::query()->get();
   }

   public static function find($id): ?static
   {
      return static::query()->where(static::$primaryKey, '=', $id)->first();
   }

   public static function findOrFail($id): static
   {
      $result = self::find($id);
      if (!$result) {
         throw new Exception("Model with ID {$id} not found");
      }
      return $result;
   }

   public static function where($column, $value)
   {
      return static::query()->where($column, "=", $value)->first();
   }

   // ----- Filtering and Counting -----
   public static function count(): int
   {
      $sql = "SELECT COUNT(*) FROM " . static::$table;
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->execute();
      return (int) $stmt->fetchColumn();
   }

   // Pagination
   public static function paginate(int $page = 1, int $perPage = 10): Collection
   {
      $offset = ($page - 1) * $perPage;
      $sql = "SELECT * FROM " . static::$table . " LIMIT :limit OFFSET :offset";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
      $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();
      return new Collection($stmt->fetchAll(PDO::FETCH_CLASS, static::class));
   }

   public static function first(): ?static
   {
      return static::query()->first();
   }

   public static function exists(array $conditions): bool
   {
      $whereClause = [];
      $params = [];
      foreach ($conditions as $column => $value) {
         $whereClause[] = "$column = :$column";
         $params[":$column"] = $value;
      }

      $sql = "SELECT COUNT(*) FROM " . static::$table;
      if (!empty($whereClause)) {
         $sql .= " WHERE " . implode(" AND ", $whereClause);
      }

      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->execute($params);
      return (bool)$stmt->fetchColumn();
   }

   public static function filter(array $conditions): Collection
   {
      $whereClause = [];
      $params = [];
      foreach ($conditions as $column => $value) {
         $whereClause[] = "$column = :$column";
         $params[":$column"] = $value;
      }

      $sql = "SELECT * FROM " . static::$table;
      if (!empty($whereClause)) {
         $sql .= " WHERE " . implode(" AND ", $whereClause);
      }

      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->execute($params);
      return new Collection($stmt->fetchAll(PDO::FETCH_CLASS, static::class));
   }

   public static function query(): Builder
   {
      return new Builder(static::class);
   }

   // Relationships

   /**
    * Définit une relation un-à-un.
    *
    * @param string $related Classe du modèle relié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $localKey Clé locale
    * @return \IronFlow\Iron\Relations\HasOne
    */
   public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
   {
      $foreignKey = $foreignKey ?? $this->getForeignKey();
      $localKey = $localKey ?? $this->getKeyName();

      $instance = new $related();

      return new HasOne($this, $instance, $foreignKey, $localKey);
   }

   /**
    * Définit une relation un-à-plusieurs.
    *
    * @param string $related Classe du modèle relié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $localKey Clé locale
    * @return \IronFlow\Iron\Relations\HasMany
    */
   public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
   {
      $foreignKey = $foreignKey ?? $this->getForeignKey();
      $localKey = $localKey ?? $this->getKeyName();

      $instance = new $related();

      return new HasMany($this, $instance, $foreignKey, $localKey);
   }

   /**
    * Définit une relation plusieurs-à-un.
    *
    * @param string $related Classe du modèle relié
    * @param string|null $foreignKey Clé étrangère
    * @param string|null $ownerKey Clé du propriétaire
    * @return \IronFlow\Iron\Relations\BelongsTo
    */
   public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
   {
      $foreignKey = $foreignKey ?? $this->guessBelongsToForeignKey();
      $ownerKey = $ownerKey ?? (new $related())->getKeyName();

      $instance = new $related();

      return new BelongsTo($this, $instance, $foreignKey, $ownerKey);
   }

   /**
    * Définit une relation plusieurs-à-plusieurs.
    *
    * @param string $related Classe du modèle relié
    * @param string|null $table Table pivot
    * @param string|null $foreignPivotKey Clé étrangère dans la table pivot
    * @param string|null $relatedPivotKey Clé du modèle relié dans la table pivot
    * @param string|null $parentKey Clé du modèle parent
    * @param string|null $relatedKey Clé du modèle relié
    * @return \IronFlow\Iron\Relations\BelongsToMany
    */
   public function belongsToMany(
      string $related,
      ?string $table = null,
      ?string $foreignPivotKey = null,
      ?string $relatedPivotKey = null,
      ?string $parentKey = null,
      ?string $relatedKey = null
   ): BelongsToMany {
      $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
      $relatedPivotKey = $relatedPivotKey ?? (new $related())->getForeignKey();

      if (is_null($table)) {
         $table = $this->joiningTable($related);
      }

      $parentKey = $parentKey ?? $this->getKeyName();
      $relatedKey = $relatedKey ?? (new $related())->getKeyName();

      $instance = new $related();

      return new BelongsToMany(
         $this,
         $instance,
         $table,
         $foreignPivotKey,
         $relatedPivotKey,
         $parentKey,
         $relatedKey
      );
   }

   /**
    * Définit une relation has-many-through.
    *
    * @param string $related Classe du modèle final
    * @param string $through Classe du modèle intermédiaire
    * @param string|null $firstKey Clé étrangère du premier modèle
    * @param string|null $secondKey Clé étrangère du second modèle
    * @param string|null $localKey Clé locale
    * @param string|null $secondLocalKey Clé locale du second modèle
    * @return \IronFlow\Iron\Relations\HasManyThrough
    */
   public function hasManyThrough(
      string $related,
      string $through,
      ?string $firstKey = null,
      ?string $secondKey = null,
      ?string $localKey = null,
      ?string $secondLocalKey = null
   ): HasManyThrough {
      $firstKey = $firstKey ?? $this->getForeignKey();
      $secondKey = $secondKey ?? (new $through())->getForeignKey();

      $localKey = $localKey ?? $this->getKeyName();
      $secondLocalKey = $secondLocalKey ?? (new $through())->getKeyName();

      $throughInstance = new $through();
      $relatedInstance = new $related();

      return new HasManyThrough(
         $this,
         $relatedInstance,
         $through,
         $firstKey,
         $secondKey,
         $localKey,
         $secondLocalKey
      );
   }

   /**
    * Obtient la clé étrangère pour ce modèle.
    *
    * @return string
    */
   protected function getForeignKey(): string
   {
      return strtolower(class_basename($this)) . '_' . $this->getKeyName();
   }

   /**
    * Obtient le nom de la clé primaire.
    *
    * @return string
    */
   protected function getKeyName(): string
   {
      return static::$primaryKey;
   }

   /**
    * Devine la clé étrangère pour une relation belongsTo.
    *
    * @return string
    */
   protected function guessBelongsToForeignKey(): string
   {
      $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
      return $caller . '_' . $this->getKeyName();
   }

   /**
    * Génère le nom de la table pivot pour une relation belongsToMany.
    *
    * @param string $related Classe du modèle relié
    * @return string
    */
   protected function joiningTable(string $related): string
   {
      $models = [
         strtolower(class_basename($this)),
         strtolower(class_basename($related))
      ];
      sort($models);

      return implode('_', $models);
   }

   /**
    * Charge des relations en même temps que le modèle principal (eager loading)
    * 
    * @param array|string $relations Relations à charger
    * @return \IronFlow\Iron\Query\Builder
    */
   public static function with($relations): Builder
   {
      $relations = is_string($relations) ? func_get_args() : $relations;
      $query = static::query()->with($relations);

      return $query;
   }

   /**
    * Définit une relation sur ce modèle
    * 
    * @param string $relation Nom de la relation
    * @param mixed $value Valeur de la relation
    * @return $this
    */
   public function setRelation(string $relation, $value): self
   {
      $this->relations[$relation] = $value;
      return $this;
   }

   /**
    * Récupère une relation chargée sur ce modèle
    * 
    * @param string $relation Nom de la relation
    * @return mixed|null
    */
   public function getRelation(string $relation)
   {
      return $this->relations[$relation] ?? null;
   }

   /**
    * Détermine si la clé donnée est une relation chargée
    * 
    * @param string $key Clé à vérifier
    * @return bool
    */
   public function isRelation(string $key): bool
   {
      return array_key_exists($key, $this->relations) || method_exists($this, $key);
   }

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
      return isset($this->attributes[$key]);
   }

   public function __unset(string $key): void
   {
      unset($this->attributes[$key]);
   }

   public function __call(string $method, array $arguments): mixed
   {
      if (method_exists($this, $method)) {
         return $this->$method(...$arguments);
      }
      return null;
   }

   public function __toString(): string
   {
      return json_encode($this->attributes);
   }

   /**
    * Formate une date ou une chaîne en objet DateTime en une chaîne au format 'Y-m-d H:i:s'.
    *
    * @param mixed $date Une instance de \DateTime ou une chaîne représentant une date.
    * @return string|null Retourne la date formatée ou null si l'entrée est invalide.
    * @throws Exception
    */
   private static function formatDateToString($date): ?string
   {
      if ($date instanceof DateTime) {
         return $date->format('Y-m-d H:i:s');
      }

      if (is_string($date)) {
         try {
            $parsedDate = new DateTime($date);
            return $parsedDate->format('Y-m-d H:i:s');
         } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
         }
      }

      // Si l'entrée n'est ni une chaîne ni un objet DateTime
      return null;
   }
}
