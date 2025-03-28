<?php

declare(strict_types=1);

namespace IronFlow\Iron\Relations;

use IronFlow\Iron\Model;

class HasMany extends Relation
{
   public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
   {
      parent::__construct($parent, $related, $foreignKey, $localKey);
   }

   public function getResults()
   {
      return $this->query()
         ->where($this->qualifiedForeignKeyName(), '=', $this->parentKey())
         ->get();
   }

   public function create(array $attributes): Model
   {
      $attributes[$this->foreignKeyName()] = $this->parentKey();
      return $this->related()->create($attributes);
   }

   public function save(Model $model): bool
   {
      $model->setAttribute($this->foreignKeyName(), $this->parentKey());
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
      $instances = $this->getResults();
      if ($instances === null || count($instances) === 0) {
         return false;
      }

      $success = true;
      foreach ($instances as $instance) {
         $instanceSuccess = $instance::update(array_merge(
            $attributes,
            [$this->foreignKeyName() => $this->parentKey()]
         ));
         $success = $success && $instanceSuccess;
      }

      return $success;
   }

   public function delete(): bool
   {
      $instances = $this->getResults();
      if ($instances === null || count($instances) === 0) {
         return false;
      }

      $success = true;
      foreach ($instances as $instance) {
         $instanceSuccess = $instance::delete($instance->getAttribute($this->related()->getKeyName()));
         $success = $success && $instanceSuccess;
      }

      return $success;
   }

   public function count(): int
   {
      return $this->query()
         ->where($this->qualifiedForeignKeyName(), '=', $this->parentKey())
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
