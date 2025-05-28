<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeders;

use PDO;
use Closure;
use Throwable;
use IronFlow\Database\Connection;
use IronFlow\Database\Exceptions\SeederException;

/**
 * Classe de base pour les seeders
 */
abstract class Seeder
{
    protected PDO $connection;
    protected bool $useTransactions = true;
    protected array $dependencies = [];

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::getInstance()->getConnection();
    }

    /**
     * Exécute le seeder
     */
    abstract public function run(): void;

    /**
     * Définit les dépendances du seeder
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Vérifie si le seeder utilise les transactions
     */
    public function shouldUseTransactions(): bool
    {
        return $this->useTransactions;
    }

    /**
     * Définit la connexion à utiliser
     */
    public function setConnection(PDO $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Exécute le seeder avec gestion des transactions
     */
    public function execute(?Closure $progressCallback = null): void
    {
        if ($this->shouldUseTransactions()) {
            $this->runInTransaction($progressCallback);
        } else {
            $this->runWithoutTransaction($progressCallback);
        }
    }

    /**
     * Exécute le seeder dans une transaction
     */
    protected function runInTransaction(?Closure $progressCallback = null): void
    {
        $this->connection->beginTransaction();

        try {
            $this->run();
            if ($progressCallback) {
                $progressCallback(static::class);
            }
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            throw new SeederException(
                "Seeder " . static::class . " failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Exécute le seeder sans transaction
     */
    protected function runWithoutTransaction(?Closure $progressCallback = null): void
    {
        try {
            $this->run();
            if ($progressCallback) {
                $progressCallback(static::class);
            }
        } catch (Throwable $e) {
            throw new SeederException(
                "Seeder " . static::class . " failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Appelle un autre seeder
     */
    protected function call(string|array $seeders): void
    {
        $seeders = is_array($seeders) ? $seeders : [$seeders];

        foreach ($seeders as $seederClass) {
            if (!class_exists($seederClass)) {
                throw new SeederException("Seeder class {$seederClass} does not exist");
            }

            $seeder = new $seederClass($this->connection);
            $seeder->execute();
        }
    }

    /**
     * Tronque une table
     */
    protected function truncate(string $table): void
    {
        $this->connection->exec("TRUNCATE TABLE {$table}");
    }

    /**
     * Supprime tous les enregistrements d'une table
     */
    protected function delete(string $table): void
    {
        $this->connection->exec("DELETE FROM {$table}");
    }

    /**
     * Vérifie si une table existe
     */
    protected function tableExists(string $table): bool
    {
        $stmt = $this->connection->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Compte les enregistrements dans une table
     */
    protected function count(string $table): int
    {
        $stmt = $this->connection->query("SELECT COUNT(*) FROM {$table}");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Insère des données dans une table
     */
    protected function insert(string $table, array $data): void
    {
        if (empty($data)) {
            return;
        }

        // Si c'est un tableau associatif simple
        if (!isset($data[0])) {
            $data = [$data];
        }

        $keys = array_keys($data[0]);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO {$table} (" . implode(', ', $keys) . ") VALUES ({$placeholders})";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $row) {
            $stmt->execute($row);
        }
    }

    /**
     * Exécute une requête SQL brute
     */
    protected function query(string $sql, array $params = []): bool
    {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obtient la connexion à la base de données
     */
    protected function getConnection(): PDO
    {
        return $this->connection;
    }
}
