<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use PDO;
use Exception;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;

/**
 * Classe pour exécuter les migrations
 */
class Migrator
{
   /**
    * Instance de la connexion à la base de données
    *
    * @var PDO
    */
   protected PDO $connection;

   /**
    * Répertoire contenant les migrations
    *
    * @var string
    */
   protected string $migrationsPath;

   /**
    * Nom de la table contenant les migrations exécutées
    *
    * @var string
    */
   protected string $migrationsTable = 'migrations';

   /**
    * Constructeur
    *
    * @param PDO $connection Connexion à la base de données
    * @param string $migrationsPath Chemin vers les migrations
    */
   public function __construct(PDO $connection, string $migrationsPath)
   {
      $this->connection = $connection;
      $this->migrationsPath = rtrim($migrationsPath, '/\\');

      $this->ensureMigrationsTableExists();
   }

   /**
    * Exécute toutes les migrations en attente
    *
    * @return array Migrations exécutées
    */
   public function migrate(): array
   {
      $migrations = $this->getPendingMigrations();
      $migrationsRun = [];

      foreach ($migrations as $migration) {
         $this->runMigration($migration);
         $migrationsRun[] = $migration;
      }

      return $migrationsRun;
   }

   /**
    * Annule la dernière migration
    *
    * @param int $steps Nombre d'étapes à annuler
    * @return array Migrations annulées
    */
   public function rollback(int $steps = 1): array
   {
      $migrations = $this->getRecentMigrations($steps);
      $migrationsRolledBack = [];

      foreach ($migrations as $migration) {
         $this->rollbackMigration($migration);
         $migrationsRolledBack[] = $migration;
      }

      return $migrationsRolledBack;
   }

   /**
    * Annule toutes les migrations
    *
    * @return array Migrations annulées
    */
   public function reset(): array
   {
      $migrations = $this->getCompletedMigrations();
      $migrations = array_reverse($migrations); // Annuler dans l'ordre inverse
      $migrationsRolledBack = [];

      foreach ($migrations as $migration) {
         $this->rollbackMigration($migration);
         $migrationsRolledBack[] = $migration;
      }

      return $migrationsRolledBack;
   }

   /**
    * Annule toutes les migrations et les réexécute
    *
    * @return array Migrations exécutées
    */
   public function refresh(): array
   {
      $this->reset();
      return $this->migrate();
   }

   /**
    * Vérifie si toutes les migrations ont été exécutées
    *
    * @return bool
    */
   public function isUpToDate(): bool
   {
      return count($this->getPendingMigrations()) === 0;
   }

   /**
    * Récupère toutes les migrations disponibles
    *
    * @return array
    */
   public function getAllMigrations(): array
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
    * Récupère les migrations qui ont été exécutées
    *
    * @return array
    */
   public function getCompletedMigrations(): array
   {
      $stmt = $this->connection->prepare("SELECT migration FROM {$this->migrationsTable} ORDER BY batch, migration");
      $stmt->execute();

      $migrations = [];
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
         $migrations[] = $row['migration'];
      }

      return $migrations;
   }

   /**
    * Récupère les migrations en attente
    *
    * @return array
    */
   public function getPendingMigrations(): array
   {
      $allMigrations = $this->getAllMigrations();
      $completedMigrations = $this->getCompletedMigrations();

      return array_diff($allMigrations, $completedMigrations);
   }

   /**
    * Récupère les migrations les plus récentes
    *
    * @param int $count Nombre de migrations à récupérer
    * @return array
    */
   public function getRecentMigrations(int $count = 1): array
   {
      $stmt = $this->connection->prepare(
         "SELECT migration FROM {$this->migrationsTable} ORDER BY batch DESC, migration DESC LIMIT :count"
      );
      $stmt->bindValue(':count', $count, PDO::PARAM_INT);
      $stmt->execute();

      $migrations = [];
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
         $migrations[] = $row['migration'];
      }

      return $migrations;
   }

   /**
    * Exécute une migration spécifique
    *
    * @param string $migration Nom de la migration
    * @return bool
    */
   protected function runMigration(string $migration): bool
   {
      $file = $this->migrationsPath . '/' . $migration . '.php';

      if (!file_exists($file)) {
         throw new Exception("Le fichier de migration '$migration' n'existe pas.");
      }

      try {
         // Charger la migration (avec return new class extends...)
         $migrationInstance = require $file;

         if (!$migrationInstance instanceof Migration) {
            throw new Exception("Le fichier de migration '$migration' ne retourne pas une instance de Migration.");
         }

         // Exécuter la migration
         $migrationInstance->runUp();

         // Enregistrer la migration
         $this->logMigration($migration);

         return true;
      } catch (Exception $e) {
         throw new Exception("Erreur lors de l'exécution de la migration '$migration': " . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Annule une migration spécifique
    *
    * @param string $migration Nom de la migration
    * @return bool
    */
   protected function rollbackMigration(string $migration): bool
   {
      $file = $this->migrationsPath . '/' . $migration . '.php';

      if (!file_exists($file)) {
         throw new Exception("Le fichier de migration '$migration' n'existe pas.");
      }

      try {
         // Charger la migration (avec return new class extends...)
         $migrationInstance = require $file;

         if (!$migrationInstance instanceof Migration) {
            throw new Exception("Le fichier de migration '$migration' ne retourne pas une instance de Migration.");
         }

         // Annuler la migration
         $migrationInstance->runDown();

         // Supprimer l'entrée de la migration
         $this->removeMigrationLog($migration);

         return true;
      } catch (Exception $e) {
         throw new Exception("Erreur lors de l'annulation de la migration '$migration': " . $e->getMessage(), 0, $e);
      }
   }

   /**
    * Enregistre une migration comme exécutée
    *
    * @param string $migration Nom de la migration
    * @return void
    */
   protected function logMigration(string $migration): void
   {
      $batch = $this->getNextBatchNumber();

      $stmt = $this->connection->prepare(
         "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (:migration, :batch)"
      );
      $stmt->bindValue(':migration', $migration);
      $stmt->bindValue(':batch', $batch);
      $stmt->execute();
   }

   /**
    * Supprime une migration de la table de migrations
    *
    * @param string $migration Nom de la migration
    * @return void
    */
   protected function removeMigrationLog(string $migration): void
   {
      $stmt = $this->connection->prepare(
         "DELETE FROM {$this->migrationsTable} WHERE migration = :migration"
      );
      $stmt->bindValue(':migration', $migration);
      $stmt->execute();
   }

   /**
    * Récupère le prochain numéro de batch
    *
    * @return int
    */
   protected function getNextBatchNumber(): int
   {
      $stmt = $this->connection->prepare(
         "SELECT MAX(batch) as batch FROM {$this->migrationsTable}"
      );
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return ($result['batch'] ?? 0) + 1;
   }

   /**
    * S'assure que la table de migrations existe
    *
    * @return void
    */
   protected function ensureMigrationsTableExists(): void
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
      Schema::setDefaultConnection($this->connection);

      if (!Schema::hasTable($this->migrationsTable)) {
         Schema::createTable($this->migrationsTable, function (Anvil $table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamps();
         });
      }
   }
}
