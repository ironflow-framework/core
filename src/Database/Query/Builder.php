<?php

declare(strict_types=1);

namespace IronFlow\Database\Query;

use IronFlow\Database\Connection;
use PDO;

class Builder
{
   public function __construct(
      private readonly Connection $connection,
      private readonly string $table
   ) {}

   public function insert(array $values): bool
   {
      $columns = array_keys($values);
      $placeholders = array_fill(0, count($values), '?');

      $sql = sprintf(
         "INSERT INTO %s (%s) VALUES (%s)",
         $this->table,
         implode(', ', $columns),
         implode(', ', $placeholders)
      );

      $stmt = $this->connection->getPdo()->prepare($sql);
      return $stmt->execute(array_values($values));
   }

   public function delete(string $column, mixed $value): bool
   {
      $sql = sprintf("DELETE FROM %s WHERE %s = ?", $this->table, $column);
      $stmt = $this->connection->getPdo()->prepare($sql);
      return $stmt->execute([$value]);
   }

   public function get(): array
   {
      $sql = sprintf("SELECT * FROM %s", $this->table);
      return $this->connection->getPdo()->query($sql)->fetchAll();
   }

   public function where(string $column, mixed $value): array
   {
      $sql = sprintf("SELECT * FROM %s WHERE %s = ?", $this->table, $column);
      $stmt = $this->connection->getPdo()->prepare($sql);
      $stmt->execute([$value]);
      return $stmt->fetchAll();
   }

   public function orderBy(string $column, string $direction = 'ASC'): array
   {
      $sql = sprintf("SELECT * FROM %s ORDER BY %s %s", $this->table, $column, $direction);
      return $this->connection->getPdo()->query($sql)->fetchAll();
   }
}
