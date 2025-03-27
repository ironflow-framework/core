<?php

declare(strict_types=1);

namespace IronFlow\Database\Relations;

use IronFlow\Database\Model;
use IronFlow\Database\Iron\Builder;

abstract class Relation
{
   protected Model $parent;
   protected Model $related;
   protected Builder $query;
   protected string $foreignKey;
   protected string $localKey;

   public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
   {
      $this->parent = $parent;
      $this->related = $related;
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;
      $this->query = $related->query();
   }

   abstract public function getResults();

   public function getQuery(): Builder
   {
      return $this->query;
   }

   public function getParent(): Model
   {
      return $this->parent;
   }

   public function getRelated(): Model
   {
      return $this->related;
   }

   public function getForeignKey(): string
   {
      return $this->foreignKey;
   }

   public function getLocalKey(): string
   {
      return $this->localKey;
   }

   protected function getParentKey()
   {
      return $this->parent->getAttribute($this->localKey);
   }

   protected function getParentKeyName(): string
   {
      return $this->localKey;
   }

   protected function getForeignKeyName(): string
   {
      return $this->foreignKey;
   }

   

   protected function getQualifiedForeignKeyName(): string
   {
      return $this->related->getTable() . '.' . $this->foreignKey;
   }

   protected function getQualifiedParentKeyName(): string
   {
      return $this->parent->getTable() . '.' . $this->localKey;
   }
}
