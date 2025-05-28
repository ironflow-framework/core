<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use PDO;
use Throwable;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Exceptions\MigrationException;

/**
 * Gestionnaire de migrations de base de données
 * 
 * Responsable de l'exécution, du rollback et du suivi des migrations
 */
class Migrator
{
   protected PDO $connection;
   protected string $migrationsPath;
   protected string $migrationsTable = 'migrations';
   protected Logger $logger;
   protected array $loadedMigrations = [];

   public function __construct(PDO $connection, string $migrationsPath)
   {
      $this->connection = $connection;
      $this->migrationsPath = rtrim($migrationsPath, '/\\');
      $this->initializeLogger();
      $this->ensureMigrationsTableExists();
   }

   /**
    * Exécute toutes les migrations en attente
    */
   public function migrate(): MigrationResult
   {
      $pendingMigrations = $this->getPendingMigrations();

      if (empty($pendingMigrations)) {
         $this->logger->info('No pending migrations found.');
         return new MigrationResult([], 'No migrations to run');
      }

      $result = new MigrationResult();

      foreach ($pendingMigrations as $migrationName) {
         try {
            $this->executeMigration($migrationName, 'up');
            $result->addSuccess($migrationName);
            $this->logger->info("Migration completed: {$migrationName}");
         } catch (Throwable $e) {
            $errorMsg = "Migration failed: {$migrationName} - {$e->getMessage()}";
            $this->logger->error($errorMsg, ['exception' => $e]);
            $result->addError($migrationName, $e);

            if ($this->shouldStopOnError()) {
               break;
            }
         }
      }

      return $result;
   }

   /**
    * Annule les migrations récentes
    */
   public function rollback(int $steps = 1): MigrationResult
   {
      $migrationsToRollback = $this->getRecentMigrations($steps);
      $result = new MigrationResult();

      foreach ($migrationsToRollback as $migrationName) {
         try {
            $this->executeMigration($migrationName, 'down');
            $this->removeMigrationLog($migrationName);
            $result->addSuccess($migrationName);
            $this->logger->info("Migration rolled back: {$migrationName}");
         } catch (Throwable $e) {
            $errorMsg = "Rollback failed: {$migrationName} - {$e->getMessage()}";
            $this->logger->error($errorMsg, ['exception' => $e]);
            $result->addError($migrationName, $e);
            break; // Stop on rollback errors
         }
      }

      return $result;
   }

   /**
    * Remet à zéro toutes les migrations
    */
   public function reset(): MigrationResult
   {
      $completedMigrations = array_reverse($this->getCompletedMigrations());
      $result = new MigrationResult();

      foreach ($completedMigrations as $migrationName) {
         try {
            $this->executeMigration($migrationName, 'down');
            $this->removeMigrationLog($migrationName);
            $result->addSuccess($migrationName);
         } catch (Throwable $e) {
            $result->addError($migrationName, $e);
            break;
         }
      }

      return $result;
   }

   /**
    * Remet à zéro et réexécute toutes les migrations
    */
   public function refresh(): MigrationResult
   {
      $resetResult = $this->reset();
      if ($resetResult->hasErrors()) {
         return $resetResult;
      }

      return $this->migrate();
   }

   /**
    * Vérifie le statut des migrations
    */
   public function status(): MigrationStatus
   {
      $allMigrations = $this->getAllMigrations();
      $completedMigrations = $this->getCompletedMigrations();
      $pendingMigrations = array_diff($allMigrations, $completedMigrations);

      return new MigrationStatus(
         $allMigrations,
         $completedMigrations,
         $pendingMigrations
      );
   }

   /**
    * Vérifie si des migrations sont en attente
    */
   public function isDirty(): bool
   {
      return !empty($this->getPendingMigrations());
   }

   /**
    * Vérifie si toutes les migrations sont à jour
    */
   public function isUpToDate(): bool
   {
      return empty($this->getPendingMigrations());
   }

   /**
    * Exécute une migration spécifique
    */
   protected function executeMigration(string $migrationName, string $direction): void
   {
      $migration = $this->loadMigration($migrationName);

      $this->connection->beginTransaction();

      try {
         if ($direction === 'up') {
            $migration->up();
            $this->logMigration($migrationName);
         } else {
            $migration->down();
         }

         $this->connection->commit();
      } catch (Throwable $e) {
         $this->connection->rollBack();
         throw new MigrationException(
            "Migration {$direction} failed for {$migrationName}: " . $e->getMessage(),
            0,
            $e
         );
      }
   }

   /**
    * Charge une migration depuis un fichier
    */
   protected function loadMigration(string $migrationName): Migration
   {
      if (isset($this->loadedMigrations[$migrationName])) {
         return $this->loadedMigrations[$migrationName];
      }

      $filePath = $this->migrationsPath . '/' . $migrationName . '.php';

      if (!file_exists($filePath)) {
         throw new MigrationException("Migration file not found: {$filePath}");
      }

      $migrationInstance = require $filePath;

      if (!$migrationInstance instanceof Migration) {
         throw new MigrationException(
            "Migration file {$migrationName} must return an instance of Migration"
         );
      }

      $this->loadedMigrations[$migrationName] = $migrationInstance;
      return $migrationInstance;
   }

   /**
    * Récupère toutes les migrations disponibles
    */
   protected function getAllMigrations(): array
   {
      $files = glob($this->migrationsPath . '/*.php');
      $migrations = [];

      foreach ($files as $file) {
         $migrations[] = pathinfo($file, PATHINFO_FILENAME);
      }

      sort($migrations);
      return $migrations;
   }

   /**
    * Récupère les migrations terminées
    */
   protected function getCompletedMigrations(): array
   {
      $stmt = $this->connection->prepare(
         "SELECT migration FROM {$this->migrationsTable} ORDER BY batch ASC, migration ASC"
      );
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_COLUMN);
   }

   /**
    * Récupère les migrations en attente
    */
   protected function getPendingMigrations(): array
   {
      $allMigrations = $this->getAllMigrations();
      $completedMigrations = $this->getCompletedMigrations();

      return array_diff($allMigrations, $completedMigrations);
   }

   /**
    * Récupère les migrations récentes
    */
   protected function getRecentMigrations(int $count): array
   {
      $stmt = $this->connection->prepare(
         "SELECT migration FROM {$this->migrationsTable} 
             ORDER BY batch DESC, migration DESC 
             LIMIT :count"
      );
      $stmt->bindValue(':count', $count, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_COLUMN);
   }

   /**
    * Enregistre une migration comme terminée
    */
   protected function logMigration(string $migrationName): void
   {
      $batch = $this->getNextBatchNumber();
      $stmt = $this->connection->prepare(
         "INSERT INTO {$this->migrationsTable} (migration, batch, executed_at) 
             VALUES (:migration, :batch, :executed_at)"
      );

      $stmt->execute([
         ':migration' => $migrationName,
         ':batch' => $batch,
         ':executed_at' => date('Y-m-d H:i:s')
      ]);
   }

   /**
    * Supprime l'enregistrement d'une migration
    */
   protected function removeMigrationLog(string $migrationName): void
   {
      $stmt = $this->connection->prepare(
         "DELETE FROM {$this->migrationsTable} WHERE migration = :migration"
      );
      $stmt->execute([':migration' => $migrationName]);
   }

   /**
    * Récupère le prochain numéro de batch
    */
   protected function getNextBatchNumber(): int
   {
      $stmt = $this->connection->prepare(
         "SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM {$this->migrationsTable}"
      );
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return (int) $result['next_batch'];
   }

   /**
    * Crée la table de migrations si elle n'existe pas
    */
   protected function ensureMigrationsTableExists(): void
   {
      Schema::setDefaultConnection($this->connection);

      if (!Schema::hasTable($this->migrationsTable)) {
         Schema::createTable($this->migrationsTable, function (Anvil $table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index('migration');
            $table->index('batch');
         });
      }
   }

   /**
    * Initialise le logger
    */
   protected function initializeLogger(): void
   {
      $this->logger = new Logger('migrations');
      $this->logger->pushHandler(
         new StreamHandler(storage_path('logs/migrations.log'), Logger::INFO)
      );
   }

   /**
    * Détermine si l'exécution doit s'arrêter en cas d'erreur
    */
   protected function shouldStopOnError(): bool
   {
      return true; // Configurable via config
   }
}
