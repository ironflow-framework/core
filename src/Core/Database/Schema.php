<?php

namespace IronFlow\Core\Database;

use PDOException;
use InvalidArgumentException;
use IronFlow\Core\Database\Schema\Anvil;
use RuntimeException;

/**
 * Enhanced Schema class with better error handling and logging
 */
class Schema
{
    /**
     * Create a new table
     */
    public static function create(string $table, callable $callback): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $anvil = new Anvil($table);
            
            // Execute the callback to build the schema
            $callback($anvil);
            
            // Generate and execute SQL
            $sql = $anvil->toSql();
            
            // Log the SQL for debugging (in development)
            if (defined('APP_DEBUG') && env('APP_ENV') === 'development') {
                error_log("Creating table: " . $sql);
            }
            
            $pdo->exec($sql);
            
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create table '{$table}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Drop a table
     */
    public static function drop(string $table): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Sanitize table name
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
                throw new InvalidArgumentException("Invalid table name: {$table}");
            }
            
            $sql = "DROP TABLE IF EXISTS `{$table}`";
            
            if (defined('APP_DEBUG') && env('APP_ENV') === 'development') {
                error_log("Dropping table: " . $sql);
            }
            
            $pdo->exec($sql);
            
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to drop table '{$table}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if a table exists
     */
    public static function hasTable(string $table): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
                throw new InvalidArgumentException("Invalid table name: {$table}");
            }
            
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check table existence '{$table}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if a column exists in a table
     */
    public static function hasColumn(string $table, string $column): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) || 
                !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
                throw new InvalidArgumentException("Invalid table or column name");
            }
            
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $stmt->execute([$column]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to check column existence '{$column}' in table '{$table}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Truncate a table
     */
    public static function truncate(string $table): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
                throw new InvalidArgumentException("Invalid table name: {$table}");
            }
            
            $sql = "TRUNCATE TABLE `{$table}`";
            $pdo->exec($sql);
            
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to truncate table '{$table}': " . $e->getMessage(), 0, $e);
        }
    }
}
