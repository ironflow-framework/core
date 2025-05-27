<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use Exception;
use IronFlow\Database\Connection;
use IronFlow\Database\Schema\Schema;

/**
 * Classe de base pour les migrations
 */
abstract class Migration
{
   protected Connection $connection;
   protected Schema $schema;

   public function __construct()
   {
      $this->connection = Connection::getInstance();
      $this->schema = new Schema();
   }

   /**
    * Exécute la migration
    *
    * @return void
    */
   abstract public function up(): void;

   /**
    * Annule la migration
    *
    * @return void
    */
   abstract public function down(): void;

   /**
    * Exécute la migration dans une transaction
    *
    * @return bool Indique si la migration s'est bien exécutée
    */
   public function runUp(): bool
   {
      try {
         // $this->beginTransaction();
         $this->up();
         // $this->commitTransaction();
         return true;
      } catch (Exception $e) {
         $this->rollbackTransaction();
         throw $e;
      }
   }

   /**
    * Annule la migration dans une transaction
    *
    * @return bool Indique si la migration a bien été annulée
    */
   public function runDown(): bool
   {
      try {
         $this->beginTransaction();
         $this->down();
         $this->commitTransaction();
         return true;
      } catch (Exception $e) {
         $this->rollbackTransaction();
         throw $e;
      }
   }

   /**
    * Démarre une transaction
    *
    * @return void
    */
   protected function beginTransaction(): void
   {
      $this->connection->getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    *
    * @return void
    */
   protected function commitTransaction(): void
   {
      $this->connection->getConnection()->beginTransaction();
      $this->connection->getConnection()->commit();
   }

   /**
    * Annule une transaction
    *
    * @return void
    */
   protected function rollbackTransaction(): void
   {
      if ($this->connection->getConnection()->inTransaction()) {
         $this->connection->getConnection()->rollBack();
      }
   }

   /**
    * Exécute une requête SQL brute
    *
    * @param string $sql Requête SQL
    * @param array $params Paramètres de la requête
    * @return bool Succès de l'exécution
    */
   protected function rawQuery(string $sql, array $params = []): bool
   {
      $stmt = $this->connection->getConnection()->prepare($sql);
      return $stmt->execute($params);
   }

   /**
    * Obtient une instance du constructeur de schéma
    */
   protected function schema(): Schema
   {
      return $this->schema;
   }

   /**
    * Obtient une instance de la connexion à la base de données
    */
   protected function connection(): Connection
   {
      return $this->connection;
   }
}
