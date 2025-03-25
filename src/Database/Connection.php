<?php

namespace IronFlow\Database;

use PDO;

class Connection
{
   private static ?Connection $instance = null;
   private PDO $connection;

   private function __construct()
   {
      if (config('database.host') === null || config('database.name') === null || config('database.username') === null || config('database.password') === null) {
         throw new \Exception('Database configuration is missing');
      }

      try {
         switch (config('database.driver')) {
            case 'mysql':
               $this->connection = new PDO('mysql:host=' . config('database.host') . ';dbname=' . config('database.name'), config('database.username'), config('database.password'));
               break;
            case 'pgsql':
               $this->connection = new PDO('pgsql:host=' . config('database.host') . ';dbname=' . config('database.name'), config('database.username'), config('database.password'));
               break;
            case 'sqlite':
               $this->connection = new PDO('sqlite:' . config('database.name'));
               break;
            default:
               throw new \Exception('Unsupported database driver: ' . config('database.driver'));
         }
      } catch (\PDOException $e) {
         throw new \Exception('Failed to connect to database: ' . $e->getMessage());
      }
   }

   public static function getInstance(): Connection
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function getConnection(): PDO
   {
      return $this->connection;
   }
 
}
