<?php

declare(strict_types=1);

namespace IronFlow\Database\Relations;

use IronFlow\Database\Collection;
use IronFlow\Database\Model;
use IronFlow\Database\Query\Builder;

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

   public function getResults(): ?self
   {
      return $this->query
         ->select($this->related->getTable() . '.*')
         ->join(
            $this->table,
            $this->getQualifiedRelatedKeyName(),
            '=',
            $this->relatedPivotKey
         )
         ->where($this->foreignPivotKey, '=', $this->getParentKey())
         ->get();
   }

   public function attach($id, array $attributes = []): bool
   {
      $values = $this->formatAttachRecords(
         $this->parseIds($id),
         $attributes
      );

      return $this->query->insert($values)->execute();
   }

   public function detach($ids = null): bool
   {
      $query = $this->query->where($this->foreignPivotKey, '=', $this->getParentKey());

      if ($ids !== null) {
         $ids = $this->parseIds($ids);
         $query->whereIn($this->relatedPivotKey, $ids);
      }

      return $query->delete()->execute();
   }

   public function sync($ids, bool $detaching = true)
   {
      $changes = [
         'attached' => [],
         'detached' => [],
         'updated' => []
      ];

      $current = $this->newPivotQuery()
         ->pluck($this->relatedPivotKey)
         ->all();

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

      $current = $this->newPivotQuery()
         ->pluck($this->relatedPivotKey)
         ->all();

      $detach = array_diff($current, array_keys($records));
      $attach = array_diff(array_keys($records), $current);

      if (count($detach) > 0) {
         $this->detach($detach);
         $changes['detached'] = $detach;
      }

      if (count($attach) > 0) {
         $this->attachNew($records, $current, false);
         $changes['attached'] = $attach;
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
         $this->foreignPivotKey => $this->getParentKey(),
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
         return $value->modelKeys();
      }

      if (is_array($value)) {
         return array_values($value);
      }

      return [$value];
   }

   protected function attachNew(array $records, array $current, bool $touch = true): array
   {
      $changes = ['attached' => [], 'updated' => []];

      foreach ($records as $id => $attributes) {
         if (!in_array($id, $current)) {
            $this->query->insert($attributes);
            $changes['attached'][] = $id;
         } elseif (count($attributes) > 0) {
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

      return $this->newPivotQuery()
         ->where($this->foreignPivotKey, $this->getParentKey())
         ->where($this->relatedPivotKey, $id)
         ->update($attributes)
         ->execute();
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
      return $this->query->from($this->table);
   }

   public function getQualifiedRelatedKeyName(): string
   {
      return $this->related->getTable() . '.' . $this->relatedKey;
   }

   public function getQualifiedForeignPivotKeyName(): string
   {
      return $this->table . '.' . $this->foreignPivotKey;
   }

   public function getQualifiedRelatedPivotKeyName(): string
   {
      return $this->table . '.' . $this->relatedPivotKey;
   }

   public function with(array $relations): self
   {
      $this->query->with($relations);
      return $this;
   }

   public function where($column, $operator = null, $value = null): self
   {
      $this->query->where($column, $operator, $value);
      return $this;
   }

   public function whereIn($column, array $values): self
   {
      $this->query->whereIn($column, $values);
      return $this;
   }

   public function whereNull($column): self
   {
      $this->query->whereNull($column);
      return $this;
   }

   public function whereNotNull($column): self
   {
      $this->query->whereNotNull($column);
      return $this;
   }

   public function orderBy($column, $direction = 'asc'): self
   {
      $this->query->orderBy($column, $direction);
      return $this;
   }

   public function limit($limit): self
   {
      $this->query->limit($limit);
      return $this;
   }

   public function offset($offset): self
   {
      $this->query->offset($offset);
      return $this;
   }
}
