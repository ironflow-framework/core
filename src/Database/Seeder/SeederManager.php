<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeder;

use Database\Seeder\DatabaseSeeder;
use IronFlow\Database\Connection;

class SeederManager
{
   protected Connection $connection;

   public function __construct(Connection $connection)
   {
      $this->connection = $connection;
   }

   public function run(): void
   {
      $seeder = new DatabaseSeeder($this->connection);
      $seeder->run();
   }

   public function runSpecific(string $seeder): void
   {
      $class = $this->getSeederClass($seeder);
      $instance = new $class($this->connection);
      $instance->run();
   }

   protected function getSeederClass(string $seeder): string
   {
      $file = $this->getSeedersPath() . '/' . $seeder . '.php';
      require_once $file;

      $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $seeder))) . 'Seeder';
      return "Database\\Seeder\\{$class}";
   }

   protected function getSeedersPath(): string
   {
      return database_path('seeders');
   }
}
