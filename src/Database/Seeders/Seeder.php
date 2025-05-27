<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeders;

use IronFlow\Database\Connection;
use PDO;

/**
 * Classe de base pour les seeders
 */
abstract class Seeder
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();
    }

    /**
     * Exécute le seeder
     */
    abstract public function run(): void;

    /**
     * Appelle d'autres seeders
     */
    protected function call(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            if (is_string($seeder)) {
                $seeder = new $seeder($this->db);
            }
            
            $seeder->run();
        }
    }

    /**
     * Commence une transaction
     */
    protected function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Valide la transaction
     */
    protected function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Annule la transaction
     */
    protected function rollback(): void
    {
        $this->db->rollBack();
    }
}
