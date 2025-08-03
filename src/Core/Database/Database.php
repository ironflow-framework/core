<?php

declare(strict_types=1);

namespace IronFlow\Core\Database;

use PDO;
use PDOStatement;
use PDOException;
use IronFlow\Core\Database\Exceptions\DatabaseException;
use IronFlow\Core\Database\Exceptions\ConnectionException;
use IronFlow\Core\Database\Exceptions\QueryException;

/**
 * Gestionnaire de base de données professionnel pour IronFlow
 * 
 * Features:
 * - Connection pooling
 * - Query logging
 * - Transaction management
 * - Error handling
 * - Performance monitoring
 * - Multiple database support
 */
class Database
{
    /**
     * Instance PDO principale
     */
    protected ?PDO $pdo = null;

    /**
     * Configuration des connexions
     */
    protected array $config;

    /**
     * Connexions multiples (read/write separation)
     */
    protected array $connections = [];

    /**
     * Logs des requêtes
     */
    protected array $queryLog = [];

    /**
     * Activer/désactiver le logging
     */
    protected bool $logging = false;

    /**
     * Compteur de requêtes
     */
    protected int $queryCount = 0;

    /**
     * Temps total d'exécution
     */
    protected float $totalExecutionTime = 0.0;

    /**
     * Instance singleton
     */
    protected static ?self $instance = null;

    /**
     * Constructeur
     */
    public function __construct(array $config)
    {
        $this->config = $this->normalizeConfig($config);
        $this->logging = $config['logging'] ?? false;
    }

    /**
     * Singleton pattern
     */
    public static function getInstance(?array $config = null): self
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new ConnectionException('Database configuration required for first instantiation');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Normalise la configuration
     */
    protected function normalizeConfig(array $config): array
    {
        return array_merge([
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'timezone' => '+00:00',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ],
            'read' => null,
            'write' => null,
        ], $config);
    }

    /**
     * Établit la connexion principale
     */
    protected function connect(string $type = 'write'): PDO
    {
        if (isset($this->connections[$type])) {
            return $this->connections[$type];
        }

        $config = $this->getConnectionConfig($type);

        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Configuration post-connexion
            $this->configureConnection($pdo, $config);
            
            $this->connections[$type] = $pdo;
            
            // Définir la connexion principale si c'est la première
            if ($this->pdo === null) {
                $this->pdo = $pdo;
            }
            
            return $pdo;
            
        } catch (PDOException $e) {
            throw new ConnectionException(
                "Failed to connect to database [{$type}]: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Construit le DSN
     */
    protected function buildDsn(array $config): string
    {
        $dsn = "{$config['driver']}:";
        
        $parts = [];
        if (isset($config['host'])) {
            $parts[] = "host={$config['host']}";
        }
        if (isset($config['port'])) {
            $parts[] = "port={$config['port']}";
        }
        if (isset($config['dbname'])) {
            $parts[] = "dbname={$config['dbname']}";
        }
        if (isset($config['charset'])) {
            $parts[] = "charset={$config['charset']}";
        }

        return $dsn . implode(';', $parts);
    }

    /**
     * Configure la connexion après établissement
     */
    protected function configureConnection(PDO $pdo, array $config): void
    {
        // Configuration du timezone
        if (isset($config['timezone'])) {
            $pdo->exec("SET time_zone='{$config['timezone']}'");
        }

        // Mode strict MySQL
        if ($config['strict'] ?? false) {
            $pdo->exec("SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        }

        // Autres configurations spécifiques au driver
        if ($config['driver'] === 'mysql') {
            $pdo->exec("SET SESSION sql_mode='TRADITIONAL'");
        }
    }

    /**
     * Récupère la configuration pour un type de connexion
     */
    protected function getConnectionConfig(string $type): array
    {
        $config = $this->config;
        
        // Utiliser la configuration spécifique read/write si disponible
        if ($type === 'read' && isset($this->config['read'])) {
            $config = array_merge($config, $this->config['read']);
        } elseif ($type === 'write' && isset($this->config['write'])) {
            $config = array_merge($config, $this->config['write']);
        }
        
        return $config;
    }

    /**
     * Récupère la connexion appropriée
     */
    public function getConnection(string $type = 'write'): PDO
    {
        return $this->connections[$type] ?? $this->connect($type);
    }

    /**
     * Récupère la connexion de lecture
     */
    public function getReadConnection(): PDO
    {
        return $this->getConnection('read');
    }

    /**
     * Récupère la connexion d'écriture
     */
    public function getWriteConnection(): PDO
    {
        return $this->getConnection('write');
    }

    /**
     * Exécute une requête préparée
     */
    public function query(string $sql, array $bindings = [], string $connectionType = 'write'): PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $connection = $this->getConnection($connectionType);
            $statement = $connection->prepare($sql);
            $statement->execute($bindings);
            
            $this->logQuery($sql, $bindings, microtime(true) - $startTime);
            
            return $statement;
            
        } catch (PDOException $e) {
            $this->logQuery($sql, $bindings, microtime(true) - $startTime, $e);
            throw new QueryException(
                "Query failed: {$e->getMessage()}\nSQL: {$sql}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Exécute une requête de sélection
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->query($sql, $bindings, 'read');
        return $statement->fetchAll();
    }

    /**
     * Récupère le premier résultat
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $results = $this->select($sql, $bindings);
        return $results[0] ?? null;
    }

    /**
     * Exécute une requête d'insertion
     */
    public function insert(string $sql, array $bindings = []): bool
    {
        return $this->query($sql, $bindings)->rowCount() > 0;
    }

    /**
     * Exécute une requête de mise à jour
     */
    public function update(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Exécute une requête de suppression
     */
    public function delete(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Récupère le dernier ID inséré
     */
    public function lastInsertId(): string
    {
        return $this->getWriteConnection()->lastInsertId();
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction(): bool
    {
        $this->queryCount++;
        return $this->getWriteConnection()->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit(): bool
    {
        return $this->getWriteConnection()->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollBack(): bool
    {
        return $this->getWriteConnection()->rollBack();
    }

    /**
     * Vérifie si une transaction est en cours
     */
    public function inTransaction(): bool
    {
        return $this->getWriteConnection()->inTransaction();
    }

    /**
     * Exécute du code dans une transaction
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Teste la connexion
     */
    public function ping(): bool
    {
        try {
            $this->query('SELECT 1');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Reconnecte si nécessaire
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Ferme toutes les connexions
     */
    public function disconnect(): void
    {
        $this->connections = [];
        $this->pdo = null;
    }

    /**
     * Enregistre une requête dans les logs
     */
    protected function logQuery(string $sql, array $bindings, float $time, ?\Throwable $exception = null): void
    {
        $this->queryCount++;
        $this->totalExecutionTime += $time;
        
        if (!$this->logging) {
            return;
        }

        $this->queryLog[] = [
            'query' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'exception' => $exception?->getMessage(),
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Récupère le log des requêtes
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Vide le log des requêtes
     */
    public function flushQueryLog(): void
    {
        $this->queryLog = [];
    }

    /**
     * Active/désactive le logging
     */
    public function enableQueryLog(bool $enable = true): void
    {
        $this->logging = $enable;
    }

    /**
     * Récupère le nombre de requêtes exécutées
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Récupère le temps total d'exécution
     */
    public function getTotalExecutionTime(): float
    {
        return $this->totalExecutionTime;
    }

    /**
     * Récupère les statistiques de performance
     */
    public function getStats(): array
    {
        return [
            'query_count' => $this->queryCount,
            'total_execution_time' => $this->totalExecutionTime,
            'average_execution_time' => $this->queryCount > 0 ? $this->totalExecutionTime / $this->queryCount : 0,
            'active_connections' => count($this->connections),
        ];
    }

    /**
     * Méthode magique pour déléguer à PDO
     */
    public function __call(string $method, array $arguments)
    {
        return $this->getConnection()->$method(...$arguments);
    }
}