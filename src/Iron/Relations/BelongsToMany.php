<?php

declare(strict_types=1);

namespace IronFlow\Iron\Relations;

use IronFlow\Iron\Model;
use IronFlow\Iron\Collection;
use IronFlow\Iron\Query\Builder;
use PDO;

class BelongsToMany extends Relation
{
   protected string $table;
   protected string $relatedKey;
   protected string $foreignPivotKey;
   protected string $relatedPivotKey;
   protected array $pivotColumns;

   public function __construct(
      Model $parent,
      Model $related,
      string $table,
      string $foreignPivotKey,
      string $relatedPivotKey,
      string $parentKey,
      string $relatedKey
   ) {
      $this->table = $table;
      $this->relatedKey = $relatedKey;
      $this->foreignPivotKey = $foreignPivotKey;
      $this->relatedPivotKey = $relatedPivotKey;

      parent::__construct($parent, $related, $parentKey, $relatedKey);
   }

   public function getResults()
   {
      return $this->query()
         ->select($this->related()->getTable() . '.*')
         ->join(
            $this->table,
            $this->qualifiedRelatedKeyName(),
            '=',
            $this->relatedPivotKey
         )
         ->where($this->foreignPivotKey, '=', $this->parentKey())
         ->get();
   }

   public function attach($id, array $attributes = []): bool
   {
      $values = $this->formatAttachRecords(
         $this->parseIds($id),
         $attributes
      );

      // Insertion directe en SQL plutôt que via la méthode insert
      $conn = $this->parent()->getConnection();
      $success = true;

      foreach ($values as $record) {
         $columns = implode(", ", array_keys($record));
         $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($record)));
         $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
         $stmt = $conn->prepare($sql);
         $success = $success && $stmt->execute($record);
      }

      return $success;
   }

   public function detach($ids = null): bool
   {
      $conn = $this->parent()->getConnection();
      $sql = "DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = :parentKey";
      $params = [':parentKey' => $this->parentKey()];

      if ($ids !== null) {
         $ids = $this->parseIds($ids);
         $placeholders = implode(',', array_map(function ($i) {
            return ':id' . $i;
         }, array_keys($ids)));

         $sql .= " AND {$this->relatedPivotKey} IN ($placeholders)";

         foreach ($ids as $i => $id) {
            $params[':id' . $i] = $id;
         }
      }

      $stmt = $conn->prepare($sql);
      return $stmt->execute($params);
   }

   public function sync($ids, bool $detaching = true)
   {
      $changes = [
         'attached' => [],
         'detached' => [],
         'updated' => []
      ];

      // Récupérer les IDs existants
      $sql = "SELECT {$this->relatedPivotKey} FROM {$this->table} WHERE {$this->foreignPivotKey} = :parentKey";
      $stmt = $this->parent()->getConnection()->prepare($sql);
      $stmt->execute([':parentKey' => $this->parentKey()]);
      $current = $stmt->fetchAll(PDO::FETCH_COLUMN);

      $records = $this->formatAttachRecords(
         $this->parseIds($ids),
         []
      );

      $detach = array_diff($current, array_keys($records));

      if ($detaching && count($detach) > 0) {
         $this->detach($detach);
         $changes['detached'] = $detach;
      }

      $changes = array_merge(
         $changes,
         $this->attachNew($records, $current, false)
      );

      return $changes;
   }

   public function toggle($ids): array
   {
      $changes = [
         'attached' => [],
         'detached' => []
      ];

      $records = $this->formatAttachRecords(
         $this->parseIds($ids),
         []
      );

      // Récupérer les IDs existants
      $sql = "SELECT {$this->relatedPivotKey} FROM {$this->table} WHERE {$this->foreignPivotKey} = :parentKey";
      $stmt = $this->parent()->getConnection()->prepare($sql);
      $stmt->execute([':parentKey' => $this->parentKey()]);
      $current = $stmt->fetchAll(PDO::FETCH_COLUMN);

      $detach = array_diff($current, array_keys($records));
      $attach = array_diff(array_keys($records), $current);

      if (count($detach) > 0) {
         $this->detach($detach);
         $changes['detached'] = $detach;
      }

      if (count($attach) > 0) {
         foreach ($attach as $id) {
            if (isset($records[$id])) {
               $this->attach($id, $records[$id]);
               $changes['attached'][] = $id;
            }
         }
      }

      return $changes;
   }

   protected function formatAttachRecords(array $ids, array $attributes): array
   {
      $records = [];
      $hasTimestamps = ($this->hasPivotColumn('created_at') && $this->hasPivotColumn('updated_at'));

      foreach ($ids as $key => $value) {
         $records[] = $this->attacher($key, $value, $attributes, $hasTimestamps);
      }

      return $records;
   }

   protected function attacher($id, $value, array $attributes, bool $hasTimestamps): array
   {
      $record = [
         $this->foreignPivotKey => $this->parentKey(),
         $this->relatedPivotKey => $id
      ];

      if ($hasTimestamps) {
         $record['created_at'] = date('Y-m-d H:i:s');
         $record['updated_at'] = date('Y-m-d H:i:s');
      }

      return array_merge($record, $attributes);
   }

   protected function parseIds($value): array
   {
      if ($value instanceof Model) {
         return [$value->getAttribute($this->relatedKey)];
      }

      if ($value instanceof Collection) {
         // Alternative à modelKeys
         $keys = [];
         foreach ($value as $model) {
            $keys[] = $model->getAttribute($this->relatedKey);
         }
         return $keys;
      }

      if (is_array($value)) {
         return array_values($value);
      }

      return [$value];
   }

   protected function attachNew(array $records, array $current, bool $touch = true): array
   {
      $changes = ['attached' => [], 'updated' => []];
      $conn = $this->parent()->getConnection();

      foreach ($records as $id => $attributes) {
         if (!in_array($id, $current)) {
            // Insertion directe en SQL
            $record = array_merge($attributes, [
               $this->foreignPivotKey => $this->parentKey(),
               $this->relatedPivotKey => $id
            ]);

            $columns = implode(", ", array_keys($record));
            $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($record)));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($record)) {
               $changes['attached'][] = $id;
            }
         } elseif (count($attributes) > 0) {
            // Mise à jour directe en SQL
            $this->updateExistingPivot($id, $attributes, $touch);
            $changes['updated'][] = $id;
         }
      }

      return $changes;
   }

   protected function updateExistingPivot($id, array $attributes, bool $touch): bool
   {
      if (in_array($this->updatedAt(), $this->pivotColumns)) {
         $attributes = $this->addTimestampsToAttachment($attributes, true);
      }

      $conn = $this->parent()->getConnection();

      if (empty($attributes)) {
         return true;
      }

      $setParts = [];
      $params = [
         ':parentKey' => $this->parentKey(),
         ':id' => $id
      ];

      foreach ($attributes as $key => $value) {
         $setParts[] = "$key = :set_$key";
         $params[':set_' . $key] = $value;
      }

      $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) .
         " WHERE {$this->foreignPivotKey} = :parentKey AND {$this->relatedPivotKey} = :id";

      $stmt = $conn->prepare($sql);
      return $stmt->execute($params);
   }

   protected function addTimestampsToAttachment(array $attributes, bool $exists = false): array
   {
      $attributes[$this->updatedAt()] = date('Y-m-d H:i:s');

      if (!$exists) {
         $attributes[$this->createdAt()] = date('Y-m-d H:i:s');
      }

      return $attributes;
   }

   protected function createdAt(): string
   {
      return 'updated_at';
   }

   protected function updatedAt(): string
   {
      return 'updated_at';
   }

   protected function hasPivotColumn(string $column): bool
   {
      return in_array($column, $this->pivotColumns);
   }

   protected function newPivotQuery(): Builder
   {
      // Créer une requête sur la table pivot sans utiliser from()
      $query = new Builder(new class extends Model {});
      $query->setTable($this->table);
      return $query;
   }

   /**
    * Récupère une liste de valeurs à partir d'une colonne
    */
   protected function pluck(Builder $query, string $column): array
   {
      $sql = "SELECT {$column} FROM {$this->table} WHERE {$this->foreignPivotKey} = :parentKey";
      $stmt = $this->parent()->getConnection()->prepare($sql);
      $stmt->execute([':parentKey' => $this->parentKey()]);
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
   }

   public function qualifiedRelatedKeyName(): string
   {
      return $this->related()->getTable() . '.' . $this->relatedKey;
   }

   public function qualifiedForeignPivotKeyName(): string
   {
      return $this->table . '.' . $this->foreignPivotKey;
   }

   public function qualifiedRelatedPivotKeyName(): string
   {
      return $this->table . '.' . $this->relatedPivotKey;
   }

   public function with(array $relations): self
   {
      $this->query()->with($relations);
      return $this;
   }

   public function where($column, $operator = null, $value = null): self
   {
      $this->query()->where($column, $operator, $value);
      return $this;
   }

   public function whereIn($column, array $values): self
   {
      $this->query()->whereIn($column, $values);
      return $this;
   }

   public function whereNull($column): self
   {
      $this->query()->whereNull($column);
      return $this;
   }

   public function whereNotNull($column): self
   {
      $this->query()->whereNotNull($column);
      return $this;
   }

   public function orderBy($column, $direction = 'asc'): self
   {
      $this->query()->orderBy($column, $direction);
      return $this;
   }

   public function limit($limit): self
   {
      $this->query()->limit($limit);
      return $this;
   }

   public function offset($offset): self
   {
      $this->query()->offset($offset);
      return $this;
   }
}
