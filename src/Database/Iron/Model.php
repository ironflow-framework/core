<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron;

use DateTime;
use Exception;
use IronFlow\Database\Connection;
use IronFlow\Database\Iron\Collection;
use IronFlow\Database\Iron\Query\Builder;
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

   public static function latest(string $column = 'created_at'): ?static
   {
      $sql = "SELECT * FROM " . static::$table . " ORDER BY :column DESC LIMIT 1";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':column', $column, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchObject(static::class);
   }

   public static function oldest(string $column = 'created_at'): ?static
   {
      $sql = "SELECT * FROM " . static::$table . " ORDER BY :column ASC LIMIT 1";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':column', $column, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchObject(static::class);
   }

   public static function pluck(string $column): array
   {
      $sql = "SELECT :column FROM " . static::$table;
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':column', $column, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
   }

   public static function chunk(int $size, callable $callback): void
   {
      $offset = 0;
      do {
         $sql = "SELECT * FROM " . static::$table . " LIMIT :limit OFFSET :offset";
         $stmt = (new static())->getConnection()->prepare($sql);
         $stmt->bindValue(':limit', $size, PDO::PARAM_INT);
         $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
         $stmt->execute();
         $results = $stmt->fetchAll(PDO::FETCH_CLASS, static::class);

         if (empty($results)) {
            break;
         }

         $callback($results);
         $offset += $size;
      } while (count($results) === $size);
   }

   public static function increment($id, string $column, int $amount = 1): bool
   {
      $sql = "UPDATE " . static::$table . " SET :column = :column + :amount WHERE " . static::$primaryKey . " = :id";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':column', $column, PDO::PARAM_STR);
      $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id);
      return $stmt->execute();
   }

   public static function decrement($id, string $column, int $amount = 1): bool
   {
      $sql = "UPDATE " . static::$table . " SET :column = :column - :amount WHERE " . static::$primaryKey . " = :id";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindParam(':column', $column, PDO::PARAM_STR);
      $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id);
      return $stmt->execute();
   }

   public static function withTrashed(): Collection
   {
      $sql = "SELECT * FROM " . static::$table;
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->execute();
      return new Collection($stmt->fetchAll(PDO::FETCH_CLASS, static::class));
   }

   public static function onlyTrashed(): Collection
   {
      $sql = "SELECT * FROM " . static::$table . " WHERE deleted_at IS NOT NULL";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->execute();
      return new Collection($stmt->fetchAll(PDO::FETCH_CLASS, static::class));
   }

   public static function restore($id): bool
   {
      $sql = "UPDATE " . static::$table . " SET deleted_at = NULL WHERE " . static::$primaryKey . " = :id";
      $stmt = (new static())->getConnection()->prepare($sql);
      $stmt->bindValue(':id', $id);
      return $stmt->execute();
   }

   // Filtrer les enregistrements
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
