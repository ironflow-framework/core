<?php

declare(strict_types=1);

namespace IronFlow\Database\Relations;

use IronFlow\Database\Model;
use IronFlow\Database\Query\Builder;

class HasMany extends Relation
{
   public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
   {
      parent::__construct($parent, $related, $foreignKey, $localKey);
   }

   public function getResults(): ?self
   {
      return $this->query
         ->where($this->getQualifiedForeignKeyName(), '=', $this->getParentKey())
         ->get();
   }

   public function create(array $attributes): Model
   {
      $attributes[$this->getForeignKeyName()] = $this->getParentKey();
      return $this->related->create($attributes);
   }

   public function save(Model $model): bool
   {
      $model->setAttribute($this->getForeignKeyName(), $this->getParentKey());
      return $model->save();
   }

   public function saveMany(array $models): array
   {
      $saved = [];
      foreach ($models as $model) {
         if ($this->save($model)) {
            $saved[] = $model;
         }
      }
      return $saved;
   }

   public function update(array $attributes): bool
   {
      return $this->query
         ->where($this->getQualifiedForeignKeyName(), '=', $this->getParentKey())
         ->update($attributes)
         ->execute();
   }

   public function delete(): bool
   {
      return $this->query
         ->where($this->getQualifiedForeignKeyName(), '=', $this->getParentKey())
         ->delete()
         ->execute();
   }

   public function count(): int
   {
      return $this->query
         ->where($this->getQualifiedForeignKeyName(), '=', $this->getParentKey())
         ->count();
   }

   public function exists(): bool
   {
      return $this->count() > 0;
   }

   public function doesntExist(): bool
   {
      return !$this->exists();
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
