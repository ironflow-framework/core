<?php

declare(strict_types=1);

namespace IronFlow\Database;

use PDO;
use PDOException;

/**
 * Classe de gestion de la connexion à la base de données
 * 
 * Cette classe implémente le pattern Singleton pour fournir une instance unique
 * de connexion à la base de données dans toute l'application. Elle gère la connexion
 * à différents types de bases de données (MySQL, PostgreSQL, SQLite, SQL Server)
 * et fournit des méthodes utilitaires pour les opérations courantes.
 * 
 * @package IronFlow\Database
 * @author IronFlow Team
 * @version 1.0.0
 */
class Connection
{
   /**
    * Instance unique de la connexion
    * 
    * @var Connection|null
    */
   private static ?Connection $instance = null;

   /**
    * Instance PDO de connexion à la base de données
    * 
    * @var PDO|null
    */
   private ?PDO $connection = null;

   /**
    * Configuration de la connexion à la base de données
    * 
    * @var array<string, mixed>
    */
   private array $config;

   /**
    * Constructeur privé pour empêcher l'instanciation directe
    * 
    * @throws PDOException Si la connexion échoue
    */
   private function __construct()
   {
      $this->config = config('database', []);
      $this->connect();
   }

   /**
    * Clone privé pour empêcher le clonage
    */
   private function __clone() {}

   /**
    * Récupère l'instance unique de la connexion
    * 
    * @return Connection
    */
   public static function getInstance(): Connection
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }

      return self::$instance;
   }

   /**
    * Établit la connexion à la base de données
    * 
    * @throws PDOException Si la connexion échoue
    */
   private function connect(): void
   {
      $driver = $this->config['driver'] ?? 'mysql';
      $host = $this->config['host'] ?? 'localhost';
      $port = $this->config['port'] ?? '3306';
      $database = $this->config['database'] ?? 'ironflow';
      $username = $this->config['username'] ?? 'root';
      $password = $this->config['password'] ?? '';
      $charset = $this->config['charset'] ?? 'utf8mb4';
      $options = $this->config['options'] ?? [];

      $defaultOptions = [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false,
      ];

      $options = array_merge($defaultOptions, $options);

      try {
         $dsn = $this->buildDsn($driver, $host, $port, $database, $charset);
         $this->connection = new PDO($dsn, $username, $password, $options);
      } catch (PDOException $e) {
         throw new PDOException("Impossible de se connecter à la base de données: " . $e->getMessage());
      }
   }

   /**
    * Construit la chaîne DSN pour la connexion PDO
    * 
    * @param string $driver Driver de base de données
    * @param string $host Hôte de la base de données
    * @param string $port Port de la base de données
    * @param string $database Nom de la base de données
    * @param string $charset Jeu de caractères
    * @return string
    * @throws \InvalidArgumentException Si le driver n'est pas supporté
    */
   private function buildDsn(string $driver, string $host, string $port, string $database, string $charset): string
   {
      switch ($driver) {
         case 'mysql':
            return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
         case 'pgsql':
            return "pgsql:host={$host};port={$port};dbname={$database}";
         case 'sqlite':
            return "sqlite:{$database}";
         case 'sqlsrv':
            return "sqlsrv:Server={$host},{$port};Database={$database}";
         default:
            throw new \InvalidArgumentException("Driver de base de données non supporté: {$driver}");
      }
   }

   /**
    * Récupère l'instance PDO de connexion
    * 
    * @return PDO
    */
   public function getConnection(): PDO
   {
      if ($this->connection === null) {
         $this->connect();
      }

      return $this->connection;
   }

   /**
    * Ferme la connexion à la base de données
    * 
    * @return void
    */
   public function closeConnection(): void
   {
      $this->connection = null;
   }

   /**
    * Démarre une transaction
    * 
    * @return bool
    * @throws PDOException Si la transaction ne peut pas être démarrée
    */
   public function beginTransaction(): bool
   {
      return $this->getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    * 
    * @return bool
    * @throws PDOException Si la transaction ne peut pas être validée
    */
   public function commit(): bool
   {
      return $this->getConnection()->commit();
   }

   /**
    * Annule une transaction
    * 
    * @return bool
    * @throws PDOException Si la transaction ne peut pas être annulée
    */
   public function rollBack(): bool
   {
      return $this->getConnection()->rollBack();
   }

   /**
    * Vérifie si une transaction est active
    * 
    * @return bool
    */
   public function inTransaction(): bool
   {
      return $this->getConnection()->inTransaction();
   }

   /**
    * Exécute une requête SQL et retourne un tableau de résultats
    * 
    * @param string $query Requête SQL
    * @param array<string, mixed> $params Paramètres de la requête
    * @return array<array<string, mixed>>
    * @throws PDOException Si la requête échoue
    */
   public function query(string $query, array $params = []): array
   {
      $stmt = $this->getConnection()->prepare($query);
      $stmt->execute($params);
      return $stmt->fetchAll();
   }

   /**
    * Exécute une requête SQL et retourne le nombre de lignes affectées
    * 
    * @param string $query Requête SQL
    * @param array<string, mixed> $params Paramètres de la requête
    * @return int
    * @throws PDOException Si la requête échoue
    */
   public function execute(string $query, array $params = []): int
   {
      $stmt = $this->getConnection()->prepare($query);
      $stmt->execute($params);
      return $stmt->rowCount();
   }

   /**
    * Récupère le dernier ID inséré
    * 
    * @param string|null $name Nom de la séquence (pour PostgreSQL)
    * @return string
    */
   public function lastInsertId(?string $name = null): string
   {
      return $this->getConnection()->lastInsertId($name);
   }

   /**
    * Insère des données dans une table
    * 
    * @param string $table Nom de la table
    * @param array<string, mixed> $data Données à insérer
    * @return bool
    * @throws PDOException Si l'insertion échoue
    */
   public function insert(string $table, array $data): bool
   {
      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO " . $table . "($columns) VALUES ($placeholders)";

      $stmt = $this->getConnection()->prepare($sql);
      return $stmt->execute($data);
   }

   /**
    * Met à jour des données dans une table
    * 
    * @param string $table Nom de la table
    * @param array<string, mixed> $data Données à mettre à jour
    * @param string $where Condition WHERE
    * @param array<string, mixed> $whereParams Paramètres de la condition WHERE
    * @return bool
    * @throws PDOException Si la mise à jour échoue
    */
   public function update(string $table, array $data, string $where, array $whereParams = []): bool
   {
      $set = implode(", ", array_map(fn($col) => "$col = :$col", array_keys($data)));
      $sql = "UPDATE $table SET $set WHERE $where";

      $stmt = $this->getConnection()->prepare($sql);
      return $stmt->execute(array_merge($data, $whereParams));
   }

   /**
    * Supprime des données d'une table
    * 
    * @param string $table Nom de la table
    * @param string $where Condition WHERE
    * @param array<string, mixed> $params Paramètres de la condition WHERE
    * @return bool
    * @throws PDOException Si la suppression échoue
    */
   public function delete(string $table, string $where, array $params = []): bool
   {
      $sql = "DELETE FROM $table WHERE $where";
      $stmt = $this->getConnection()->prepare($sql);
      return $stmt->execute($params);
   }

   /**
    * Vérifie si une table existe
    * 
    * @param string $table Nom de la table
    * @return bool
    */
   public function tableExists(string $table): bool
   {
      $driver = $this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
            $result = $this->query("SHOW TABLES LIKE ?", [$table]);
            return !empty($result);

         case 'sqlite':
            $result = $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table]);
            return !empty($result);

         case 'pgsql':
            $result = $this->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)", [$table]);
            return (bool) $result[0]['exists'];

         default:
            return false;
      }
   }

   /**
    * Exécute une requête dans une transaction
    * 
    * @param callable $callback Fonction à exécuter dans la transaction
    * @return mixed
    * @throws \Throwable Si une erreur survient
    */
   public function transaction(callable $callback)
   {
      try {
         $this->beginTransaction();
         $result = $callback();
         $this->commit();
         return $result;
      } catch (\Throwable $e) {
         $this->rollBack();
         throw $e;
      }
   }
}
