<?php 

namespace IronFlow\Database\Migrations;
use PDO;
use IronFlow\Database\Schema\Schema;

/**
* Classe de base pour les migrations
*/
abstract class Migration
{
protected PDO $connection;
protected Schema $schema;

public function __construct()
{
$this->connection = \IronFlow\Database\Connection::getInstance()->getConnection();
$this->schema = new Schema();
}

/**
* Exécute la migration
*/
abstract public function up(): void;

/**
* Annule la migration
*/
abstract public function down(): void;

/**
* Obtient le schéma
*/
protected function schema(): Schema
{
return $this->schema;
}

/**
* Exécute une requête SQL brute
*/
protected function rawQuery(string $sql, array $params = []): bool
{
$stmt = $this->connection->prepare($sql);
return $stmt->execute($params);
}
}