<?php

declare(strict_types=1);

namespace IronFlow\Database;

use IronFlow\Database\Migration\MigrationManager;
use IronFlow\Database\Seeder\SeederManager;

class DatabaseManager
{
   protected array $connections = [];
   protected array $config;

   public function __construct(array $config)
   {
      $this->config = $config;
   }

   public function connection(?string $name = null): Connection
   {
      $name = $name ?: $this->config['default'];

      if (!isset($this->connections[$name])) {
         $this->connections[$name] = new Connection($this->config['connections'][$name]);
      }

      return $this->connections[$name];
   }

   public function migrate(): void
   {
      $manager = new MigrationManager($this->connection());
      $manager->run();
   }

   public function rollback(): void
   {
      $manager = new MigrationManager($this->connection());
      $manager->rollback();
   }

   public function reset(): void
   {
      $manager = new MigrationManager($this->connection());
      $manager->reset();
   }

   public function refresh(): void
   {
      $manager = new MigrationManager($this->connection());
      $manager->refresh();
   }

   public function seed(): void
   {
      $manager = new SeederManager($this->connection());
      $manager->run();
   }

   public function seedSpecific(string $seeder): void
   {
      $manager = new SeederManager($this->connection());
      $manager->runSpecific($seeder);
   }

   public function beginTransaction(): bool
   {
      return $this->connection()->beginTransaction();
   }

   public function commit(): bool
   {
      return $this->connection()->commit();
   }

   public function inTransaction(): bool
   {
      return $this->connection()->inTransaction();
   }

   public function lastInsertId(?string $name = null): string
   {
      return $this->connection()->lastInsertId($name);
   }

   public function quote(string $string): string
   {
      return $this->connection()->quote($string);
   }

   public function exec(string $sql): int|false
   {
      return $this->connection()->exec($sql);
   }

   public function query(string $sql): \PDOStatement
   {
      return $this->connection()->query($sql);
   }

   public function prepare(string $sql): \PDOStatement
   {
      return $this->connection()->prepare($sql);
   }
}
