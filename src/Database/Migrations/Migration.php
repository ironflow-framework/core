<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use PDO;
use Exception;
use IronFlow\Database\Schema\Schema;

/**
 * Classe de base pour les migrations
 */
abstract class Migration
{
   /**
    * Instance de la connexion à la base de données
    *
    * @var PDO
    */
   protected PDO $connection;

   /**
    * Constructeur
    *
    * @param PDO $connection Connexion à la base de données
    */
   public function __construct(PDO $connection)
   {
      $this->connection = $connection;

      // Configurer la connexion statique dans Schema si elle n'est pas déjà configurée
      Schema::setDefaultConnection($connection);
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
         $this->beginTransaction();
         $this->up();
         $this->commitTransaction();
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
      $this->connection->beginTransaction();
   }

   /**
    * Valide une transaction
    *
    * @return void
    */
   protected function commitTransaction(): void
   {
      $this->connection->commit();
   }

   /**
    * Annule une transaction
    *
    * @return void
    */
   protected function rollbackTransaction(): void
   {
      if ($this->connection->inTransaction()) {
         $this->connection->rollBack();
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
      $stmt = $this->connection->prepare($sql);
      return $stmt->execute($params);
   }
}
