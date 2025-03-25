<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use IronFlow\Database\Connection;
use IronFlow\Database\Schema\Schema;

class MigrationManager
{
   protected Connection $connection;
   protected Schema $schema;

   public function __construct(Connection $connection)
   {
      $this->connection = $connection;
      $this->schema = new Schema();
   }

   public function run(): void
   {
      $this->createMigrationsTable();

      $migrations = $this->getPendingMigrations();
      foreach ($migrations as $migration) {
         $this->runMigration($migration);
      }
   }

   public function rollback(): void
   {
      $migrations = $this->getLastBatchMigrations();
      foreach ($migrations as $migration) {
         $this->rollbackMigration($migration);
      }
   }

   public function reset(): void
   {
      $migrations = $this->getAllMigrations();
      foreach ($migrations as $migration) {
         $this->rollbackMigration($migration);
      }
   }

   public function refresh(): void
   {
      $this->reset();
      $this->run();
   }

   protected function createMigrationsTable(): void
   {
      if (!$this->schema->hasTable('migrations')) {
         $this->schema->createTable('migrations', function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
         });
      }
   }

   protected function getPendingMigrations(): array
   {
      $files = glob($this->getMigrationsPath() . '/*.php');
      $migrations = [];

      foreach ($files as $file) {
         $migration = $this->getMigrationFromFile($file);
         if (!$this->hasMigrationBeenRun($migration)) {
            $migrations[] = $migration;
         }
      }

      return $migrations;
   }

   protected function getLastBatchMigrations(): array
   {
      $sql = "SELECT migration FROM migrations WHERE batch = (SELECT MAX(batch) FROM migrations)";
      $stmt = $this->connection->getConnection()->query($sql);
      return $stmt->fetchAll(\PDO::FETCH_COLUMN);
   }

   protected function getAllMigrations(): array
   {
      $sql = "SELECT migration FROM migrations ORDER BY batch DESC, migration DESC";
      $stmt = $this->connection->getConnection()->query($sql);
      return $stmt->fetchAll(\PDO::FETCH_COLUMN);
   }

   protected function getMigrationsPath(): string
   {
      return database_path('migrations');
   }

   protected function getMigrationFromFile(string $file): string
   {
      return basename($file, '.php');
   }

   protected function hasMigrationBeenRun(string $migration): bool
   {
      $sql = "SELECT COUNT(*) FROM migrations WHERE migration = ?";
      $stmt = $this->connection->getConnection()->prepare($sql);
      $stmt->execute([$migration]);
      return (bool) $stmt->fetchColumn();
   }

   protected function runMigration(string $migration): void
   {
      $class = $this->getMigrationClass($migration);
      $instance = new $class($this->connection);
      $instance->run();

      $batch = $this->getNextBatchNumber();
      $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
      $stmt = $this->connection->getConnection()->prepare($sql);
      $stmt->execute([$migration, $batch]);
   }

   protected function rollbackMigration(string $migration): void
   {
      $class = $this->getMigrationClass($migration);
      $instance = new $class($this->connection);
      $instance->rollback();

      $sql = "DELETE FROM migrations WHERE migration = ?";
      $stmt = $this->connection->getConnection()->prepare($sql);
      $stmt->execute([$migration]);
   }

   protected function getMigrationClass(string $migration): string
   {
      $file = $this->getMigrationsPath() . '/' . $migration . '.php';
      require_once $file;

      $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $migration))) . 'Migration';
      return "Database\\Migration\\{$class}";
   }

   protected function getNextBatchNumber(): int
   {
      $sql = "SELECT MAX(batch) as batch FROM migrations";
      $stmt = $this->connection->getConnection()->query($sql);
      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      return (int) ($result['batch'] ?? 0) + 1;
   }
}
