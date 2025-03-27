<?php

declare(strict_types=1);

namespace IronFlow\Database\Relations;

use IronFlow\Database\Model;

class HasManyThrough extends Relation
{
   protected string $through;
   protected string $firstKey;
   protected string $secondKey;
   protected string $localKey;
   protected string $relatedKey;

   public function __construct(
      Model $parent,
      Model $related,
      string $through,
      string $firstKey,
      string $secondKey,
      string $localKey
   ) {
      $this->through = $through;
      $this->firstKey = $firstKey;
      $this->secondKey = $secondKey;
      $this->localKey = $localKey;

      parent::__construct($parent, $related, $localKey, $secondKey);
   }

   public function getResults(): ?self
   {
      return $this->query
         ->select($this->related->getTable() . '.*')
         ->join(
            $this->through,
            $this->getQualifiedFirstKeyName(),
            '=',
            $this->getQualifiedLocalKeyName()
         )
         ->join(
            $this->related->getTable(),
            $this->getQualifiedSecondKeyName(),
            '=',
            $this->getQualifiedRelatedKeyName()
         )
         ->where($this->getQualifiedLocalKeyName(), '=', $this->getParentKey())
         ->get();
   }

   public function getQualifiedFirstKeyName(): string
   {
      return $this->through . '.' . $this->firstKey;
   }

   public function getQualifiedSecondKeyName(): string
   {
      return $this->through . '.' . $this->secondKey;
   }

   public function getQualifiedLocalKeyName(): string
   {
      return $this->parent->getTable() . '.' . $this->localKey;
   }

   public function getQualifiedRelatedKeyName(): string
   {
      return $this->related->getTable() . '.' . $this->relatedKey;
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
