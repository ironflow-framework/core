<?php

namespace IronFlow\Tests\Database;

use IronFlow\Database\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
   private Connection $connection;

   protected function setUp(): void
   {
      $this->connection = new Connection([
         'driver' => 'sqlite',
         'database' => ':memory:',
         'prefix' => '',
      ]);
   }

   /**
    * Test que la connexion peut être établie
    */
   public function testConnectionCanBeEstablished(): void
   {
      $this->assertTrue($this->connection->isConnected());
   }

   /**
    * Test que la connexion peut exécuter une requête
    */
   public function testConnectionCanExecuteQuery(): void
   {
      $result = $this->connection->query('SELECT 1 as test');

      $this->assertIsArray($result);
      $this->assertCount(1, $result);
      $this->assertEquals(1, $result[0]['test']);
   }

   /**
    * Test que la connexion peut créer une table
    */
   public function testConnectionCanCreateTable(): void
   {
      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $result = $this->connection->query('SELECT name FROM sqlite_master WHERE type="table" AND name="test"');

      $this->assertCount(1, $result);
   }

   /**
    * Test que la connexion peut insérer des données
    */
   public function testConnectionCanInsertData(): void
   {
      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $this->connection->query('INSERT INTO test (name) VALUES (?)', ['Test']);

      $result = $this->connection->query('SELECT * FROM test');

      $this->assertCount(1, $result);
      $this->assertEquals('Test', $result[0]['name']);
   }

   /**
    * Test que la connexion peut mettre à jour des données
    */
   public function testConnectionCanUpdateData(): void
   {
      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $this->connection->query('INSERT INTO test (name) VALUES (?)', ['Test']);
      $this->connection->query('UPDATE test SET name = ? WHERE name = ?', ['Updated', 'Test']);

      $result = $this->connection->query('SELECT * FROM test');

      $this->assertCount(1, $result);
      $this->assertEquals('Updated', $result[0]['name']);
   }

   /**
    * Test que la connexion peut supprimer des données
    */
   public function testConnectionCanDeleteData(): void
   {
      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $this->connection->query('INSERT INTO test (name) VALUES (?)', ['Test']);
      $this->connection->query('DELETE FROM test WHERE name = ?', ['Test']);

      $result = $this->connection->query('SELECT * FROM test');

      $this->assertCount(0, $result);
   }

   /**
    * Test que la connexion peut commencer une transaction
    */
   public function testConnectionCanBeginTransaction(): void
   {
      $this->assertTrue($this->connection->beginTransaction());
   }

   /**
    * Test que la connexion peut valider une transaction
    */
   public function testConnectionCanCommitTransaction(): void
   {
      $this->connection->beginTransaction();

      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $this->connection->query('INSERT INTO test (name) VALUES (?)', ['Test']);

      $this->assertTrue($this->connection->commit());

      $result = $this->connection->query('SELECT * FROM test');

      $this->assertCount(1, $result);
   }

   /**
    * Test que la connexion peut annuler une transaction
    */
   public function testConnectionCanRollbackTransaction(): void
   {
      $this->connection->beginTransaction();

      $this->connection->query('
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL
            )
        ');

      $this->connection->query('INSERT INTO test (name) VALUES (?)', ['Test']);

      $this->assertTrue($this->connection->rollBack());

      $result = $this->connection->query('SELECT name FROM sqlite_master WHERE type="table" AND name="test"');

      $this->assertCount(0, $result);
   }
}
