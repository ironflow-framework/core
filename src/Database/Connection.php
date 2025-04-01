<?php

declare(strict_types=1);

namespace IronFlow\Database;

use PDO;
use PDOException;

/**
 * Classe de gestion de la connexion à la base de données
 * 
 * Implémente le pattern Singleton pour fournir une instance unique
 * de connexion à la base de données dans toute l'application.
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
    * @var array
    */
   private array $config;

   /**
    * Constructeur privé pour empêcher l'instanciation directe
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
    */
   public function closeConnection(): void
   {
      $this->connection = null;
   }

   /**
    * Démarre une transaction
    * 
    * @return bool
    */
   public function beginTransaction(): bool
   {
      return $this->getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    * 
    * @return bool
    */
   public function commit(): bool
   {
      return $this->getConnection()->commit();
   }

   /**
    * Annule une transaction
    * 
    * @return bool
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
    * @param array $params Paramètres de la requête
    * @return array
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
    * @param array $params Paramètres de la requête
    * @return int
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
    * Insérer des données dans une table de la base de donnée
    * @param string $table
    * @param array $data
    * @return bool
    */
   public function insert(string $table, array $data): bool
   {
      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO " . $table . "($columns) VALUES ($placeholders)";

      $stmt = $this->getConnection()->prepare($sql);

      return $stmt->execute($data);
   }

}
