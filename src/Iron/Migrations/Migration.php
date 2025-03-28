<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use IronFlow\Database\Connection;
use IronFlow\Database\Schema\Schema;

abstract class Migration
{
   protected Schema $schema;
   protected Connection $connection;

   public function __construct()
   {
      $this->connection = Connection::getInstance();
      $this->schema = new Schema();
   }

   abstract public function up(): void;

   abstract public function down(): void;

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

   public function getMigrationName(): string
   {
      $reflection = new \ReflectionClass($this);
      return str_replace('Migration', '', $reflection->getShortName());
   }

   public function getMigrationFile(): string
   {
      $reflection = new \ReflectionClass($this);
      return $reflection->getFileName();
   }

   public function getMigrationTime(): string
   {
      return date('Y_m_d_His', strtotime('now'));
   }

   public function getMigrationPath(): string
   {
      return dirname($this->getMigrationFile());
   }

   public function getMigrationBatch(): int
   {
      $sql = "SELECT MAX(batch) as batch FROM migrations";
      $stmt = $this->connection->getConnection()->query($sql);
      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      return (int) ($result['batch'] ?? 0);
   }

   public function run(): void
   {
      $this->createMigrationsTable();

      $migration = $this->getMigrationName();
      $batch = $this->getMigrationBatch() + 1;

      $this->up();

      $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
      $stmt = $this->connection->getConnection()->prepare($sql);
      $stmt->execute([$migration, $batch]);
   }

   public function rollback(): void
   {
      $this->down();

      $migration = $this->getMigrationName();
      $sql = "DELETE FROM migrations WHERE migration = ?";
      $stmt = $this->connection->getConnection()->prepare($sql);
      $stmt->execute([$migration]);
   }
}
