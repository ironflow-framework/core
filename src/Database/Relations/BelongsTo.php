<?php

declare(strict_types=1);

namespace IronFlow\Database\Relations;

use IronFlow\Database\Model;
use IronFlow\Database\Query\Builder;

class BelongsTo extends Relation
{
   public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
   {
      parent::__construct($parent, $related, $foreignKey, $localKey);
   }

   public function getResults(): ?Model
   {
      $key = $this->parent->getAttribute($this->getForeignKeyName());
      if ($key === null) {
         return null;
      }

      return $this->query
         ->where($this->getQualifiedParentKeyName(), '=', $key)
         ->first();
   }

   public function associate(Model $model): bool
   {
      $this->parent->setAttribute($this->getForeignKeyName(), $model->getAttribute($this->getLocalKey()));
      return $this->parent->save();
   }

   public function dissociate(): bool
   {
      $this->parent->setAttribute($this->getForeignKeyName(), null);
      return $this->parent->save();
   }

   public function update(array $attributes): bool
   {
      $instance = $this->getResults();
      if ($instance === null) {
         return false;
      }
      return $instance::update($attributes);
   }

   public function create(array $attributes): Model
   {
      $instance = $this->related->create($attributes);
      $this->associate($instance);
      return $instance;
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
