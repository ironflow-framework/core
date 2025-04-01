<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

use IronFlow\Database\Connection;
use PDO;

/**
 * Façade et constructeur de schéma pour les migrations
 * 
 * Cette classe permet à la fois d'utiliser le constructeur de schéma de manière statique
 * et instanciée pour les migrations et autres parties de l'application.
 */
class Schema
{
   /**
    * Instance de la connexion à la base de données
    * 
    * @var PDO|null
    */
   protected static ?PDO $staticConnection = null;

   /**
    * Instance de la connexion à la base de données
    * 
    * @var PDO
    */
   protected ?PDO $connection = null;

   /**
    * Constructeur
    * 
    * @param PDO|null $connection Instance PDO
    */
   public function __construct(?PDO $connection = null)
   {
      $this->connection = $connection ?? static::getConnection();
   }

   /**
    * Définit la connexion par défaut à utiliser
    * 
    * @param PDO $connection Instance de PDO
    * @return void
    */
   public static function setDefaultConnection(PDO $connection): void
   {
      static::$staticConnection = $connection;
   }

   /**
    * Récupère la connexion PDO
    * 
    * @return PDO
    */
   protected static function getConnection(): PDO
   {
      if (static::$staticConnection === null) {
         static::$staticConnection = Connection::getInstance()->getConnection();
      }

      return static::$staticConnection;
   }

   /**
    * Crée une nouvelle table (méthode statique)
    *
    * @param string $table Nom de la table
    * @param callable $callback Fonction de configuration de la table
    * @return void
    */
   public static function createTable(string $table, callable $callback): void
   {
      $schema = new self();
      $schema->create($table, $callback);
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
      $anvil = new Anvil($table);
      $callback($anvil);

      // Convertir le plan en requête SQL
      $statements = $anvil->toSql($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));

      // Exécuter les requêtes SQL
      foreach ($statements as $statement) {
         $this->connection->exec($statement);
      }
   }

   /**
    * Modifie une table existante (méthode statique)
    *
    * @param string $table Nom de la table
    * @param callable $callback Fonction de modification de la table
    * @return void
    */
   public static function table(string $table, callable $callback): void
   {
      $schema = new self();
      $schema->modify($table, $callback);
   }

   /**
    * Modifie une table existante
    * 
    * @param string $table Nom de la table
    * @param callable $callback Fonction de modification de la table
    * @return void
    */
   public function modify(string $table, callable $callback): void
   {
      $anvil = new Anvil($table, true);
      $callback($anvil);

      // Convertir le plan en requête SQL
      $statements = $anvil->toSql($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));

      // Exécuter les requêtes SQL
      foreach ($statements as $statement) {
         $this->connection->exec($statement);
      }
   }

   /**
    * Supprime une table (méthode statique)
    *
    * @param string $table Nom de la table
    * @return void
    */
   public static function dropTable(string $table): void
   {
      $schema = new self();
      $schema->drop($table);
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
    * Supprime une table si elle existe (méthode statique)
    *
    * @param string $table Nom de la table
    * @return void
    */
   public static function dropTableIfExists(string $table): void
   {
      $schema = new self();
      $schema->dropIfExists($table);
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
    * Vérifie si une table existe (méthode statique)
    *
    * @param string $table Nom de la table
    * @return bool
    */
   public static function hasTable(string $table): bool
   {
      $schema = new self();
      return $schema->tableExists($table);
   }

   /**
    * Vérifie si une table existe
    * 
    * @param string $table Nom de la table
    * @return bool
    */
   public function tableExists(string $table): bool
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
    * Vérifie si une colonne existe dans une table (méthode statique)
    *
    * @param string $table Nom de la table
    * @param string $column Nom de la colonne
    * @return bool
    */
   public static function hasColumn(string $table, string $column): bool
   {
      $schema = new self();
      return $schema->columnExists($table, $column);
   }

   /**
    * Vérifie si une colonne existe dans une table
    * 
    * @param string $table Nom de la table
    * @param string $column Nom de la colonne
    * @return bool
    */
   public function columnExists(string $table, string $column): bool
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
    * Renomme une table (méthode statique)
    *
    * @param string $from Nom actuel de la table
    * @param string $to Nouveau nom de la table
    * @return void
    */
   public static function renameTable(string $from, string $to): void
   {
      $schema = new self();
      $schema->rename($from, $to);
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
         case 'sqlite':
            $sql = "ALTER TABLE $from RENAME TO $to";
            break;
         default:
            throw new \RuntimeException("Driver de base de données non pris en charge: $driver");
      }

      $this->connection->exec($sql);
   }
}
