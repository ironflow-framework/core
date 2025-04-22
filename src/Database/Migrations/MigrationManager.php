<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use IronFlow\Database\Connection;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Migrations\Exceptions\MigrationException;

class MigrationManager
{
   private static ?MigrationManager $instance = null;
   private Connection $connection;
   private string $table = 'migrations';
   private string $path;
   private array $migrations = [];

   private function __construct()
   {
      $this->connection = Connection::getInstance();
      $this->path = database_path('migrations');
      $this->ensureMigrationTableExists();
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   private function ensureMigrationTableExists(): void
   {
      if (!$this->connection->hasTable($this->table)) {
         $schema = new Schema();
         $schema->create($this->table, function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('executed_at')->useCurrent();
         });
      }
   }

   public function migrate(bool $pretend = false): array
   {
      $this->loadMigrationFiles();
      $ran = $this->getRanMigrations();
      $migrations = [];

      foreach ($this->migrations as $file) {
         $migration = $this->getMigrationName($file);

         if (!in_array($migration, $ran)) {
            $this->runMigration($file, $pretend);
            $migrations[] = $migration;
         }
      }

      return $migrations;
   }

   public function rollback(int $steps = 1, bool $pretend = false): array
   {
      $migrations = $this->getRanMigrations();
      $rolledBack = [];

      if (empty($migrations)) {
         return [];
      }

      // Récupérer les migrations à annuler
      $result = $this->connection->query(
         "SELECT * FROM {$this->table} ORDER BY batch DESC LIMIT ?",
         [$steps]
      );

      foreach ($result as $batch) {
         $migration = require $this->path . '/' . $batch['migration'] . '.php';

         if (!$pretend) {
            $migration->down();
            $this->connection->delete(
               $this->table,
               'migration = ?',
               [$batch['migration']]
            );
         }

         $rolledBack[] = $batch['migration'];
      }

      return $rolledBack;
   }

   public function reset(bool $pretend = false): array
   {
      $migrations = $this->getRanMigrations();
      $rolledBack = [];

      foreach (array_reverse($migrations) as $migration) {
         $file = $this->path . '/' . $migration . '.php';

         if (file_exists($file)) {
            $instance = require $file;

            if (!$pretend) {
               $instance->down();
               $this->connection->delete(
                  $this->table,
                  'migration = ?',
                  [$migration]
               );
            }

            $rolledBack[] = $migration;
         }
      }

      return $rolledBack;
   }

   public function refresh(bool $pretend = false): array
   {
      $this->reset($pretend);
      return $this->migrate($pretend);
   }

   public function fresh(bool $pretend = false): array
   {
      // Supprimer toutes les tables
      $tables = $this->connection->query("SHOW TABLES");
      foreach ($tables as $table) {
         $tableName = reset($table);
         $this->connection->query("DROP TABLE IF EXISTS {$tableName}");
      }

      return $this->migrate($pretend);
   }

   private function loadMigrationFiles(): void
   {
      $this->migrations = [];
      $files = glob($this->path . '/*.php');

      foreach ($files as $file) {
         $this->migrations[] = $file;
      }

      sort($this->migrations);
   }

   private function getRanMigrations(): array
   {
      $result = $this->connection->query(
         "SELECT migration FROM {$this->table} ORDER BY batch ASC"
      );

      return array_column($result, 'migration');
   }

   private function getMigrationName(string $path): string
   {
      return str_replace('.php', '', basename($path));
   }

   private function runMigration(string $file, bool $pretend = false): void
   {
      $migration = require $file;

      if (!$migration instanceof Migration) {
         throw new MigrationException("Le fichier de migration doit retourner une instance de Migration");
      }

      if (!$pretend) {
         $migration->up();

         $this->connection->insert($this->table, [
            'migration' => $this->getMigrationName($file),
            'batch' => $this->getNextBatchNumber()
         ]);
      }
   }

   private function getNextBatchNumber(): int
   {
      $result = $this->connection->query(
         "SELECT MAX(batch) as max_batch FROM {$this->table}"
      );

      return (int)($result[0]['max_batch'] ?? 0) + 1;
   }

   public function getMigrationFiles(): array
   {
      return $this->migrations;
   }

   public function setMigrationPath(string $path): void
   {
      $this->path = $path;
   }

   public function setMigrationTable(string $table): void
   {
      $this->table = $table;
   }
}
