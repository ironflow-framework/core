<?php

declare(strict_types=1);

namespace IronFlow\Database;

use PDO;
use PDOException;
use IronFlow\Database\Query\Builder;
use IronFlow\Database\Schema\SchemaBuilder;

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
   private readonly array $config;

   /**
    * Constructeur privé pour empêcher l'instanciation directe
    * 
    * @throws PDOException Si la connexion échoue
    */
   public function __construct()
   {
      $config = config('database', []);
      $defaultConnection = $config['default'] ?? 'mysql';
      $this->config = $config['connections'][$defaultConnection] ?? [];

      if (empty($this->config)) {
         throw new \RuntimeException("Configuration de base de données invalide pour la connexion [{$defaultConnection}]");
      }

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
         self::$instance = new self([]);
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
      $dsn = $this->buildDsn();
      $this->connection = new PDO(
         $dsn,
         $this->config['username'] ?? null,
         $this->config['password'] ?? null,
         [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
         ]
      );
   }

   /**
    * Construit la chaîne DSN pour la connexion PDO
    * 
    * @return string
    * @throws \RuntimeException Si le driver n'est pas supporté
    */
   private function buildDsn(): string
   {
      return match ($this->config['driver']) {
         'mysql' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->config['host'],
            $this->config['port'] ?? 3306,
            $this->config['database']
         ),
         'pgsql' => sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $this->config['host'],
            $this->config['port'] ?? 5432,
            $this->config['database']
         ),
         'sqlite' => sprintf('sqlite:%s', $this->config['database']),
         default => throw new \RuntimeException("Driver non supporté : {$this->config['driver']}")
      };
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
    * Alias de getConnection() pour la compatibilité
    * 
    * @return PDO
    */
   public function getPdo(): PDO
   {
      return $this->getConnection();
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

   public function table(string $table): Builder
   {
      return new Builder($this, $table);
   }

   /**
    * Retourne une instance de SchemaBuilder pour la table spécifiée
    * 
    * @param string $table Nom de la table
    * @return SchemaBuilder
    */
   public function schema(string $table): SchemaBuilder
   {
      return new SchemaBuilder($this, $table);
   }

   public function hasTable(string $table): bool
   {
      $sql = match ($this->config['driver']) {
         'mysql' => "SHOW TABLES LIKE ?",
         'pgsql' => "SELECT to_regclass(?)",
         'sqlite' => "SELECT name FROM sqlite_master WHERE type='table' AND name = ?",
      };

      $result = $this->query($sql, [$table]);
      return !empty($result);
   }
}
