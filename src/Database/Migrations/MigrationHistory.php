<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use IronFlow\Database\Connection;

class MigrationHistory
{
   private const TABLE_NAME = 'migrations';

   public function __construct(
      private readonly Connection $connection
   ) {
      $this->ensureHistoryTableExists();
   }

   public function add(string $migration): void
   {
      $this->connection->insert(self::TABLE_NAME, [
         'migration' => $migration,
         'batch' => $this->getNextBatchNumber(),
         'executed_at' => date('Y-m-d H:i:s')
      ]);
   }

   public function remove(string $migration): void
   {
      $this->connection->delete(
         self::TABLE_NAME,
         'migration = ?',
         [$migration]
      );
   }

   public function getMigrated(): array
   {
      $result = $this->connection->query(
         "SELECT migration FROM " . self::TABLE_NAME . " ORDER BY batch, migration"
      );

      return array_column($result, 'migration');
   }

   public function getLastBatch(): array
   {
      $lastBatch = $this->getLastBatchNumber();
      $result = $this->connection->query(
         "SELECT migration FROM " . self::TABLE_NAME . " WHERE batch = ? ORDER BY migration DESC",
         [$lastBatch]
      );

      return array_column($result, 'migration');
   }

   private function getNextBatchNumber(): int
   {
      return $this->getLastBatchNumber() + 1;
   }

   private function getLastBatchNumber(): int
   {
      $result = $this->connection->query(
         "SELECT MAX(batch) as max_batch FROM " . self::TABLE_NAME
      );

      return (int)($result[0]['max_batch'] ?? 0);
   }

   private function ensureHistoryTableExists(): void
   {
      if ($this->connection->tableExists(self::TABLE_NAME)) {
         return;
      }

      $this->connection->query("
         CREATE TABLE " . self::TABLE_NAME . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            batch INTEGER NOT NULL,
            executed_at DATETIME NOT NULL
         )
      ");
   }
}
