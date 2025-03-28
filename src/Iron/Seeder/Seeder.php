<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeder;

use IronFlow\Database\Connection;

abstract class Seeder
{
   protected Connection $connection;

   public function __construct(Connection $connection)
   {
      $this->connection = $connection;
   }

   abstract public function run(): void;

   public function getSeederName(): string
   {
      $reflection = new \ReflectionClass($this);
      return str_replace('Seeder', '', $reflection->getShortName());
   }

   public function getSeederFile(): string
   {
      $reflection = new \ReflectionClass($this);
      return $reflection->getFileName();
   }

   public function getSeederPath(): string
   {
      return dirname($this->getSeederFile());
   }

   public function call(string $seeder): void
   {
      $seederClass = new $seeder($this->connection);
      $seederClass->run();
   }

   public function callInOrder(array $seeders): void
   {
      foreach ($seeders as $seeder) {
         $this->call($seeder);
      }
   }
}
