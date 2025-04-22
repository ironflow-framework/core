<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

use IronFlow\Database\Connection;
use PDO;

class SchemaBuilder
{
   private array $columns = [];
   private array $indexes = [];
   private array $foreignKeys = [];

   public function __construct(
      private readonly Connection $connection,
      private readonly string $table
   ) {}

   public function create(): bool
   {
      $sql = sprintf(
         "CREATE TABLE IF NOT EXISTS %s (%s)",
         $this->table,
         $this->compileColumns()
      );

      return $this->connection->getPdo()->exec($sql) !== false;
   }

   public function drop(): bool
   {
      $sql = sprintf("DROP TABLE IF EXISTS %s", $this->table);
      return $this->connection->getPdo()->exec($sql) !== false;
   }

   public function integer(string $column): self
   {
      $this->columns[$column] = "INTEGER";
      return $this;
   }

   public function string(string $column, int $length = 255): self
   {
      $this->columns[$column] = sprintf("VARCHAR(%d)", $length);
      return $this;
   }

   public function text(string $column): self
   {
      $this->columns[$column] = "TEXT";
      return $this;
   }

   public function timestamp(string $column): self
   {
      $this->columns[$column] = "TIMESTAMP";
      return $this;
   }

   public function primary(string $column): self
   {
      $this->columns[$column] .= " PRIMARY KEY";
      return $this;
   }

   public function autoIncrement(string $column): self
   {
      $this->columns[$column] .= " AUTO_INCREMENT";
      return $this;
   }

   public function nullable(string $column): self
   {
      $this->columns[$column] .= " NULL";
      return $this;
   }

   public function notNull(string $column): self
   {
      $this->columns[$column] .= " NOT NULL";
      return $this;
   }

   public function default(string $column, mixed $value): self
   {
      $this->columns[$column] .= sprintf(
         " DEFAULT %s",
         is_string($value) ? "'$value'" : $value
      );
      return $this;
   }

   private function compileColumns(): string
   {
      return implode(', ', array_map(
         fn($column, $type) => "$column $type",
         array_keys($this->columns),
         array_values($this->columns)
      ));
   }
}
