<?php

declare(strict_types=1);

namespace IronFlow\Database;


use PDO;
use PDOException;
use RuntimeException;
use Throwable;
use InvalidArgumentException;
use IronFlow\Database\Query\Builder;
use IronFlow\Database\Schema\Schema;
use IronFlow\Support\Facades\Config;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../helpers.php';

/**
 * Gestionnaire Singleton de connexion à la base de données.
 * 
 * Fournit une interface complète pour la gestion des connexions à la base de données
 * avec support des transactions, du cache de requêtes, et de la reconnexion automatique.
 * 
 * @package IronFlow\Database
 * @author IronFlow Team
 * @version 2.0.0
 */
final class Connection
{
   private static ?Connection $instance = null;
   private ?PDO $connection = null;
   private readonly array $config;
   private LoggerInterface $logger;
   private array $queryCache = [];
   private bool $cacheEnabled = false;
   private int $cacheSize = 100;
   private int $reconnectAttempts = 3;
   private float $reconnectDelay = 1.0;
   private array $statistics = [
      'queries_executed' => 0,
      'transactions_started' => 0,
      'transactions_committed' => 0,
      'transactions_rolled_back' => 0,
      'cache_hits' => 0,
      'cache_misses' => 0,
      'reconnections' => 0,
   ];

   /**
    * Constructeur privé — initialise la connexion.
    *
    * @throws PDOException
    * @throws RuntimeException
    */
   private function __construct(?LoggerInterface $logger = null)
   {
      $this->logger = $logger ?? new NullLogger();

      Config::load();
      $config = config('database', []);
      $defaultConnection = $config['default'] ?? 'default';
      $this->config = $config['connections'][$defaultConnection] ?? [];

      if (empty($this->config)) {
         $message = "Configuration de base de données invalide pour la connexion [{$defaultConnection}]";
         $this->logger->critical($message);
         throw new RuntimeException($message);
      }

      $this->cacheEnabled = (bool)($this->config['cache']['enabled'] ?? false);
      $this->cacheSize = (int)($this->config['cache']['size'] ?? 100);
      $this->reconnectAttempts = (int)($this->config['reconnect']['attempts'] ?? 3);
      $this->reconnectDelay = (float)($this->config['reconnect']['delay'] ?? 1.0);

      $this->connect();
   }

   /**
    * Empêche le clonage de l'instance.
    */
   private function __clone() {}

   /**
    * Empêche la désérialisation de l'instance.
    */
   public function __wakeup(): void
   {
      throw new RuntimeException("La désérialisation du singleton Connection n'est pas autorisée");
   }

   /**
    * Obtient l'instance unique de Connection.
    *
    * @param LoggerInterface|null $logger
    * @return Connection
    */
   public static function getInstance(?LoggerInterface $logger = null): Connection
   {
      if (self::$instance === null) {
         self::$instance = new self($logger);
      }

      return self::$instance;
   }

   /**
    * Réinitialise l'instance (utile pour les tests).
    */
   public static function resetInstance(): void
   {
      if (self::$instance !== null) {
         self::$instance->closeConnection();
         self::$instance = null;
      }
   }

   /**
    * Établit la connexion à la base de données avec retry automatique.
    *
    * @throws PDOException
    */
   private function connect(): void
   {
      $attempts = 0;
      $lastException = null;

      while ($attempts < $this->reconnectAttempts) {
         try {
            $dsn = $this->buildDsn();
            $options = $this->buildConnectionOptions();

            $this->connection = new PDO(
               $dsn,
               $this->config['username'] ?? null,
               $this->config['password'] ?? null,
               $options
            );

            $this->logger->info("Connexion à la base de données établie", [
               'driver' => $this->config['driver'],
               'host' => $this->config['host'] ?? 'N/A',
               'database' => $this->config['database'] ?? 'N/A'
            ]);

            if ($attempts > 0) {
               $this->statistics['reconnections']++;
            }

            return;
         } catch (PDOException $e) {
            $attempts++;
            $lastException = $e;

            $this->logger->warning("Échec de connexion à la base de données", [
               'attempt' => $attempts,
               'max_attempts' => $this->reconnectAttempts,
               'error' => $e->getMessage()
            ]);

            if ($attempts < $this->reconnectAttempts) {
               usleep((int)($this->reconnectDelay * 1000000));
            }
         }
      }

      throw new PDOException(
         "Impossible de se connecter à la base de données après {$this->reconnectAttempts} tentatives: " .
            $lastException->getMessage(),
         (int)$lastException->getCode(),
         $lastException
      );
   }

   /**
    * Construit le DSN selon le driver configuré.
    *
    * @return string
    * @throws RuntimeException
    */
   private function buildDsn(): string
   {
      return match ($this->config['driver']) {
         'mysql' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'] ?? 3306,
            $this->config['database'],
            $this->config['charset'] ?? 'utf8mb4'
         ),
         'pgsql' => sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $this->config['host'],
            $this->config['port'] ?? 5432,
            $this->config['database']
         ),
         'sqlite' => sprintf('sqlite:%s', $this->config['database']),
         'sqlsrv' => sprintf(
            'sqlsrv:Server=%s,%s;Database=%s',
            $this->config['host'],
            $this->config['port'] ?? 1433,
            $this->config['database']
         ),
         default => throw new RuntimeException("Driver non supporté : {$this->config['driver']}"),
      };
   }

   /**
    * Construit les options de connexion PDO.
    *
    * @return array
    */
   private function buildConnectionOptions(): array
   {
      $defaultOptions = [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false,
         PDO::ATTR_STRINGIFY_FETCHES => false,
      ];

      // Options spécifiques par driver
      $driverOptions = match ($this->config['driver']) {
         'mysql' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($this->config['charset'] ?? 'utf8mb4'),
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
         ],
         'pgsql' => [
            PDO::PGSQL_ATTR_DISABLE_PREPARES => false,
         ],
         default => [],
      };

      // Options personnalisées depuis la configuration
      $customOptions = $this->config['options'] ?? [];

      return array_merge($defaultOptions, $driverOptions, $customOptions);
   }

   /**
    * Obtient la connexion PDO avec vérification de validité.
    *
    * @return PDO
    * @throws PDOException
    */
   public function getConnection(): PDO
   {
      if ($this->connection === null || !$this->isConnectionAlive()) {
         $this->connect();
      }

      return $this->connection;
   }

   /**
    * Vérifie si la connexion est encore active.
    *
    * @return bool
    */
   private function isConnectionAlive(): bool
   {
      if ($this->connection === null) {
         return false;
      }

      try {
         $this->connection->query('SELECT 1');
         return true;
      } catch (PDOException) {
         return false;
      }
   }

   /**
    * Ferme la connexion à la base de données.
    */
   public function closeConnection(): void
   {
      $this->connection = null;
      $this->logger->info("Connexion à la base de données fermée");
   }

   /**
    * Obtient la configuration de la connexion.
    *
    * @return array
    */
   public function getConfig(): array
   {
      return $this->config;
   }

   /**
    * Vérifie si une connexion est établie.
    *
    * @return bool
    */
   public function isConnected(): bool
   {
      return $this->connection !== null && $this->isConnectionAlive();
   }

   /**
    * Démarre une transaction.
    *
    * @return bool
    * @throws PDOException
    */
   public function beginTransaction(): bool
   {
      $result = $this->getConnection()->beginTransaction();
      if ($result) {
         $this->statistics['transactions_started']++;
         $this->logger->debug("Transaction démarrée");
      }
      return $result;
   }

   /**
    * Valide une transaction.
    *
    * @return bool
    * @throws PDOException
    */
   public function commit(): bool
   {
      $result = $this->getConnection()->commit();
      if ($result) {
         $this->statistics['transactions_committed']++;
         $this->logger->debug("Transaction validée");
      }
      return $result;
   }

   /**
    * Annule une transaction.
    *
    * @return bool
    * @throws PDOException
    */
   public function rollBack(): bool
   {
      $result = $this->getConnection()->rollBack();
      if ($result) {
         $this->statistics['transactions_rolled_back']++;
         $this->logger->debug("Transaction annulée");
      }
      return $result;
   }

   /**
    * Vérifie si une transaction est en cours.
    *
    * @return bool
    */
   public function inTransaction(): bool
   {
      return $this->getConnection()->inTransaction();
   }

   /**
    * Exécute une requête SELECT avec cache optionnel.
    *
    * @param string $query
    * @param array $params
    * @param bool $useCache
    * @return array
    * @throws PDOException
    */
   public function query(string $query, array $params = [], bool $useCache = true): array
   {
      $cacheKey = $this->cacheEnabled && $useCache ? $this->generateCacheKey($query, $params) : null;

      // Vérification du cache
      if ($cacheKey && isset($this->queryCache[$cacheKey])) {
         $this->statistics['cache_hits']++;
         $this->logger->debug("Résultat récupéré depuis le cache", ['query' => $query]);
         return $this->queryCache[$cacheKey];
      }

      if ($cacheKey) {
         $this->statistics['cache_misses']++;
      }

      $startTime = microtime(true);

      try {
         $stmt = $this->getConnection()->prepare($query);
         $stmt->execute($params);
         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $this->statistics['queries_executed']++;
         $executionTime = microtime(true) - $startTime;

         $this->logger->debug("Requête exécutée", [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime,
            'rows_returned' => count($result)
         ]);

         // Mise en cache si activée
         if ($cacheKey) {
            $this->addToCache($cacheKey, $result);
         }

         return $result;
      } catch (PDOException $e) {
         $this->logger->error("Erreur lors de l'exécution de la requête", [
            'query' => $query,
            'params' => $params,
            'error' => $e->getMessage()
         ]);
         throw $e;
      }
   }

   /**
    * Exécute une requête de modification (INSERT, UPDATE, DELETE).
    *
    * @param string $query
    * @param array $params
    * @return int Nombre de lignes affectées
    * @throws PDOException
    */
   public function execute(string $query, array $params = []): int
   {
      $startTime = microtime(true);

      try {
         $stmt = $this->getConnection()->prepare($query);
         $stmt->execute($params);
         $rowCount = $stmt->rowCount();

         $this->statistics['queries_executed']++;
         $executionTime = microtime(true) - $startTime;

         $this->logger->debug("Requête de modification exécutée", [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime,
            'rows_affected' => $rowCount
         ]);

         // Invalidation du cache après modification
         if ($this->cacheEnabled) {
            $this->clearCache();
         }

         return $rowCount;
      } catch (PDOException $e) {
         $this->logger->error("Erreur lors de l'exécution de la requête de modification", [
            'query' => $query,
            'params' => $params,
            'error' => $e->getMessage()
         ]);
         throw $e;
      }
   }

   /**
    * Obtient l'ID de la dernière insertion.
    *
    * @param string|null $name
    * @return string
    */
   public function lastInsertId(?string $name = null): string
   {
      return $this->getConnection()->lastInsertId($name);
   }

   /**
    * Insère des données dans une table.
    *
    * @param string $table
    * @param array $data
    * @return bool
    * @throws InvalidArgumentException
    * @throws PDOException
    */
   public function insert(string $table, array $data): bool
   {
      if (empty($data)) {
         throw new InvalidArgumentException("Les données d'insertion ne peuvent pas être vides");
      }

      $this->validateTableName($table);

      $columns = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));

      $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

      $stmt = $this->getConnection()->prepare($sql);
      $result = $stmt->execute($data);

      $this->logger->info("Insertion effectuée", [
         'table' => $table,
         'data' => $data,
         'success' => $result
      ]);

      return $result;
   }

   /**
    * Met à jour des données dans une table.
    *
    * @param string $table
    * @param array $data
    * @param string $where
    * @param array $whereParams
    * @return bool
    * @throws InvalidArgumentException
    * @throws PDOException
    */
   public function update(string $table, array $data, string $where, array $whereParams = []): bool
   {
      if (empty($data)) {
         throw new InvalidArgumentException("Les données de mise à jour ne peuvent pas être vides");
      }

      if (empty($where)) {
         throw new InvalidArgumentException("La clause WHERE est obligatoire pour éviter la mise à jour de toutes les lignes");
      }

      $this->validateTableName($table);

      $set = implode(", ", array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
      $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

      $stmt = $this->getConnection()->prepare($sql);
      $result = $stmt->execute(array_merge($data, $whereParams));

      $this->logger->info("Mise à jour effectuée", [
         'table' => $table,
         'data' => $data,
         'where' => $where,
         'where_params' => $whereParams,
         'success' => $result
      ]);

      return $result;
   }

   /**
    * Supprime des données d'une table.
    *
    * @param string $table
    * @param string $where
    * @param array $params
    * @return bool
    * @throws InvalidArgumentException
    * @throws PDOException
    */
   public function delete(string $table, string $where, array $params = []): bool
   {
      if (empty($where)) {
         throw new InvalidArgumentException("La clause WHERE est obligatoire pour éviter la suppression de toutes les lignes");
      }

      $this->validateTableName($table);

      $sql = "DELETE FROM {$table} WHERE {$where}";
      $stmt = $this->getConnection()->prepare($sql);
      $result = $stmt->execute($params);

      $this->logger->info("Suppression effectuée", [
         'table' => $table,
         'where' => $where,
         'params' => $params,
         'success' => $result
      ]);

      return $result;
   }

   /**
    * Vérifie si une table existe.
    *
    * @param string $table
    * @return bool
    * @throws PDOException
    */
   public function tableExists(string $table): bool
   {
      $this->validateTableName($table);

      $driver = $this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);

      $result = match ($driver) {
         'mysql'  => $this->query("SHOW TABLES LIKE ?", [$table], false),
         'sqlite' => $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table], false),
         'pgsql'  => $this->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)", [$table], false),
         'sqlsrv' => $this->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?", [$table], false),
         default  => [],
      };

      return !empty($result) && (
         $driver !== 'pgsql' || (bool)$result[0]['exists']
      );
   }

   /**
    * Exécute une transaction sécurisée avec rollback automatique en cas d'erreur.
    *
    * @param callable $callback
    * @return mixed
    * @throws Throwable
    */
   public function transaction(callable $callback): mixed
   {
      try {
         $this->beginTransaction();
         $result = $callback($this);
         $this->commit();
         return $result;
      } catch (Throwable $e) {
         if ($this->inTransaction()) {
            $this->rollBack();
         }
         throw $e;
      }
   }

   /**
    * Crée un Query Builder pour une table.
    *
    * @param string $table
    * @return Builder
    */
   public function table(string $table): Builder
   {
      return new Builder($this, $table);
   }

   /**
    * Crée un Schema Builder.
    *
    * @return Schema
    */
   public function schema(): Schema
   {
      return new Schema($this->getConnection());
   }

   /**
    * Génère une clé de cache pour une requête.
    *
    * @param string $query
    * @param array $params
    * @return string
    */
   private function generateCacheKey(string $query, array $params): string
   {
      return 'query_' . md5($query . serialize($params));
   }

   /**
    * Ajoute un résultat au cache.
    *
    * @param string $key
    * @param array $data
    */
   private function addToCache(string $key, array $data): void
   {
      if (count($this->queryCache) >= $this->cacheSize) {
         // Supprime le premier élément (FIFO)
         array_shift($this->queryCache);
      }

      $this->queryCache[$key] = $data;
   }

   /**
    * Vide le cache des requêtes.
    */
   public function clearCache(): void
   {
      $this->queryCache = [];
      $this->logger->debug("Cache des requêtes vidé");
   }

   /**
    * Obtient les statistiques d'utilisation.
    *
    * @return array
    */
   public function getStatistics(): array
   {
      return $this->statistics;
   }

   /**
    * Obtient le cache des requêtes.
    *
    * @return array
    */
   public function getCache(): array
   {
      return $this->queryCache;
   }

   /**
    * Active ou désactive le cache des requêtes.
    *
    * @param bool $enabled
    */
   public function setCacheEnabled(bool $enabled): void
   {
      $this->cacheEnabled = $enabled;
      if (!$enabled) {
         $this->clearCache();
      }
   }

   /**
    * Définit la taille maximale du cache.
    *
    * @param int $size
    * @throws InvalidArgumentException
    */
   public function setCacheSize(int $size): void
   {
      if ($size < 1) {
         throw new InvalidArgumentException("La taille du cache doit être supérieure à 0");
      }

      $this->cacheSize = $size;

      // Ajuste le cache si nécessaire
      if (count($this->queryCache) > $size) {
         $this->queryCache = array_slice($this->queryCache, -$size, null, true);
      }
   }

   /**
    * Valide un nom de table pour éviter les injections SQL.
    *
    * @param string $table
    * @throws InvalidArgumentException
    */
   private function validateTableName(string $table): void
   {
      if (empty($table) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
         throw new InvalidArgumentException("Nom de table invalide : {$table}");
      }
   }

   /**
    * Obtient des informations sur la base de données.
    *
    * @return array
    */
   public function getDatabaseInfo(): array
   {
      $connection = $this->getConnection();

      return [
         'driver' => $connection->getAttribute(PDO::ATTR_DRIVER_NAME),
         'version' => $connection->getAttribute(PDO::ATTR_SERVER_VERSION),
         'connection_status' => $connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
         'client_version' => $connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
         'server_info' => $connection->getAttribute(PDO::ATTR_SERVER_INFO),
      ];
   }

   /**
    * Exécute une requête brute sans préparation (à utiliser avec précaution).
    *
    * @param string $query
    * @return array
    * @throws PDOException
    */
   public function raw(string $query): array
   {
      $this->logger->warning("Exécution d'une requête brute", ['query' => $query]);

      $result = $this->getConnection()->query($query);
      return $result->fetchAll();
   }

   /**
    * Optimise les performances de la base de données.
    */
   public function optimize(): void
   {
      $driver = $this->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);

      match ($driver) {
         'mysql' => $this->execute("OPTIMIZE TABLE *"),
         'sqlite' => $this->execute("VACUUM"),
         'pgsql' => $this->execute("VACUUM ANALYZE"),
         default => $this->logger->info("Optimisation non supportée pour le driver {$driver}"),
      };

      $this->logger->info("Optimisation de la base de données effectuée");
   }
}
