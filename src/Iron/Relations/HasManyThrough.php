<?php

declare(strict_types=1);

namespace IronFlow\Iron\Relations;

use IronFlow\Iron\Model;
use IronFlow\Iron\Relations\Relation;

class HasManyThrough extends Relation
{
   private string $through;
   private string $firstKey;
   private string $secondKey;
   private string $localKey;
   private string $relatedKey;

   public function __construct(
      Model $parent,
      Model $related,
      string $through,
      string $firstKey,
      string $secondKey,
      string $localKey,
      string $secondLocalKey = null
   ) {
      $this->through = $through;
      $this->firstKey = $firstKey;
      $this->secondKey = $secondKey;
      $this->localKey = $localKey;
      $this->relatedKey = $secondLocalKey ?? 'id';

      parent::__construct($parent, $related, $localKey, $secondKey);
   }

   public function getResults()
   {
      return $this->query()
         ->select($this->related()->getTable() . '.*')
         ->join(
            $this->through,
            $this->qualifiedFirstKeyName(),
            '=',
            $this->qualifiedLocalKeyName()
         )
         ->join(
            $this->related()->getTable(),
            $this->qualifiedSecondKeyName(),
            '=',
            $this->qualifiedRelatedKeyName()
         )
         ->where($this->qualifiedLocalKeyName(), '=', $this->parentKey())
         ->get();
   }

   public function qualifiedFirstKeyName(): string
   {
      return $this->through . '.' . $this->firstKey;
   }

   public function qualifiedSecondKeyName(): string
   {
      return $this->through . '.' . $this->secondKey;
   }

   public function qualifiedLocalKeyName(): string
   {
      return $this->parent()->getTable() . '.' . $this->localKey;
   }

   public function qualifiedRelatedKeyName(): string
   {
      return $this->related()->getTable() . '.' . $this->relatedKey;
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
