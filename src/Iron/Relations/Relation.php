<?php

declare(strict_types=1);

namespace IronFlow\Iron\Relations;

use IronFlow\Iron\Model;
use IronFlow\Iron\Query\Builder as QueryBuilder;

abstract class Relation
{
   private Model $parent;
   private Model $related;
   private QueryBuilder $query;
   private string $foreignKey;
   private string $localKey;

   public function __construct(Model $parent, Model $related, string $foreignKey, string $localKey)
   {
      $this->parent = $parent;
      $this->related = $related;
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;
      $this->query = $related->query();
   }

   abstract public function getResults();

   public function query(): QueryBuilder
   {
      return $this->query;
   }

   public function parent(): Model
   {
      return $this->parent;
   }

   public function related(): Model
   {
      return $this->related;
   }

   public function foreignKey(): string
   {
      return $this->foreignKey;
   }

   public function localKey(): string
   {
      return $this->localKey;
   }

   protected function parentKey()
   {
      return $this->parent->getAttribute($this->localKey);
   }

   protected function parentKeyName(): string
   {
      return $this->localKey;
   }

   protected function foreignKeyName(): string
   {
      return $this->foreignKey;
   }

   protected function qualifiedForeignKeyName(): string
   {
      return $this->related->getTable() . '.' . $this->foreignKey;
   }

   protected function qualifiedParentKeyName(): string
   {
      return $this->parent->getTable() . '.' . $this->localKey;
   }
}
