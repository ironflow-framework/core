<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

use IronFlow\Database\Connection;
use PDO;

class Schema
{
   public static function createTable(string $tableName, callable $callback): void
   {
      $anvil = new Anvil($tableName);
      $callback($anvil);
      $sql = $anvil->buildCreateTableSql();
      Connection::getInstance()->getConnection()->exec($sql);
   }

   public static function alterTable(string $tableName, callable $callback): void
   {
      $anvil = new Anvil($tableName);
      $callback($anvil);
      $sql = $anvil->buildAlterTableSQL();
      Connection::getInstance()->getConnection()->exec($sql);
   }


   public static function dropTable(string $table, bool $ifExists = false): void
   {
      $anvil = new Anvil($table);
      $anvil->setIfTableExists($ifExists);

      $sql = $anvil->buildDropTableSql();
      Connection::getInstance()->getConnection()->exec($sql);
   }

   public static function dropTableIfExists(string $table): void
   {
      return static::dropTable($table, true);
   }

   public static function renameTable(string $from, string $to): bool
   {
      $sql = sprintf('RENAME TABLE %s TO %s', $from, $to);
      return Connection::getInstance()->getConnection()->exec($sql) !== false;
   }

   public static function hasTable(string $table): bool
   {
      $sql = "SHOW TABLES LIKE ?";
      $stmt = Connection::getInstance()->getConnection()->prepare($sql);
      $stmt->execute([$table]);
      return $stmt->rowCount() > 0;
   }

   public static function hasColumn(string $table, string $column): bool
   {
      $sql = "SHOW COLUMNS FROM {$table} LIKE ?";
      $stmt = Connection::getInstance()->getConnection()->prepare($sql);
      $stmt->execute([$column]);
      return $stmt->rowCount() > 0;
   }

   public static function getColumnListing($table)
   {
      $pdo = Connection::getInstance()->getConnection();
      $query = "DESCRIBE $table";
      $stmt = $pdo->query($query);
      $columns = [];

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
         $columns[] = $row['Field'];
      }

      return $columns;
   }
}
