<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

use PDO;

/**
 * Constructeur de schéma pour les migrations
 */
class SchemaBuilder
{
   /**
    * Instance de la connexion à la base de données
    * 
    * @var PDO
    */
   protected PDO $connection;

   /**
    * Constructeur
    * 
    * @param PDO $connection Instance PDO
    */
   public function __construct(PDO $connection)
   {
      $this->connection = $connection;
   }

   /**
    * Crée une nouvelle table
    * 
    * @param string $table Nom de la table
    * @param callable $callback Fonction de configuration de la table
    * @return void
    */
   public function create(string $table, callable $callback): void
   {
      $blueprint = new Anvil($table);
      $callback($blueprint);

      // Convertir le plan en requête SQL
      $statements = $blueprint->toSql($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));

      // Exécuter les requêtes SQL
      foreach ($statements as $statement) {
         $this->connection->exec($statement);
      }
   }

   /**
    * Modifie une table existante
    * 
    * @param string $table Nom de la table
    * @param callable $callback Fonction de modification de la table
    * @return void
    */
   public function table(string $table, callable $callback): void
   {
      $blueprint = new Anvil($table, true);
      $callback($blueprint);

      // Convertir le plan en requête SQL
      $statements = $blueprint->toSql($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));

      // Exécuter les requêtes SQL
      foreach ($statements as $statement) {
         $this->connection->exec($statement);
      }
   }

   /**
    * Supprime une table
    * 
    * @param string $table Nom de la table
    * @return void
    */
   public function drop(string $table): void
   {
      $sql = "DROP TABLE $table";
      $this->connection->exec($sql);
   }

   /**
    * Supprime une table si elle existe
    * 
    * @param string $table Nom de la table
    * @return void
    */
   public function dropIfExists(string $table): void
   {
      $sql = "DROP TABLE IF EXISTS $table";
      $this->connection->exec($sql);
   }

   /**
    * Vérifie si une table existe
    * 
    * @param string $table Nom de la table
    * @return bool
    */
   public function hasTable(string $table): bool
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
            $sql = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
            break;
         case 'sqlite':
            $sql = "SELECT COUNT(*) as count FROM sqlite_master WHERE type = 'table' AND name = ?";
            break;
         case 'pgsql':
            $sql = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?";
            break;
         default:
            throw new \RuntimeException("Driver de base de données non pris en charge: $driver");
      }

      $stmt = $this->connection->prepare($sql);
      $stmt->execute([$table]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result['count'] > 0;
   }

   /**
    * Vérifie si une colonne existe dans une table
    * 
    * @param string $table Nom de la table
    * @param string $column Nom de la colonne
    * @return bool
    */
   public function hasColumn(string $table, string $column): bool
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
            $sql = "SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?";
            break;
         case 'sqlite':
            $sql = "PRAGMA table_info($table)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($columns as $col) {
               if ($col['name'] === $column) {
                  return true;
               }
            }

            return false;
         case 'pgsql':
            $sql = "SELECT COUNT(*) as count FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? AND column_name = ?";
            break;
         default:
            throw new \RuntimeException("Driver de base de données non pris en charge: $driver");
      }

      if ($driver !== 'sqlite') {
         $stmt = $this->connection->prepare($sql);
         $stmt->execute([$table, $column]);
         $result = $stmt->fetch(PDO::FETCH_ASSOC);

         return $result['count'] > 0;
      }

      return false;
   }

   /**
    * Renomme une table
    * 
    * @param string $from Nom actuel de la table
    * @param string $to Nouveau nom de la table
    * @return void
    */
   public function rename(string $from, string $to): void
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
         case 'pgsql':
            $sql = "ALTER TABLE $from RENAME TO $to";
            break;
         case 'sqlite':
            $sql = "ALTER TABLE $from RENAME TO $to";
            break;
         default:
            throw new \RuntimeException("Driver de base de données non pris en charge: $driver");
      }

      $this->connection->exec($sql);
   }
}
